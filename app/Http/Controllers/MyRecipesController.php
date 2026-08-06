<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Post;
use App\Models\PostIngredient;
use App\Models\PostStep;
use App\Models\PostStepImage;
use App\Models\User;
use App\Notifications\NewRecipeCreatedNotification;
use App\Services\CalorieEstimatorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

/**
 * "Mes recettes" — ouvert à tout utilisateur connecté, scoping par user_id uniquement.
 *
 * Les étapes (avec leurs images + vidéo) sont gérées comme des sous-ressources en
 * instant-CRUD, exactement comme PageController gère les sections d'une page :
 * on crée d'abord la recette, puis on ajoute/édite/supprime les étapes une à une
 * depuis l'écran d'édition. Ça évite d'avoir à gérer des tableaux mixtes
 * fichiers/texte dans un seul gros formulaire.
 */
class MyRecipesController extends Controller
{
    public function __construct(private CalorieEstimatorService $calorieEstimator)
    {
    }

    public function index(Request $request): Response|JsonResponse
    {
        $posts = Post::query()
            ->where('user_id', Auth::id())
            ->with('categories:id,name')
            ->withAvg('ratings', 'rating')
            ->withCount('ratings')
            ->latest()
            ->paginate(12);

        // Appelé en fetch() depuis le scroll infini — réponse JSON directe,
        // sans passer par Inertia, donc aucune manipulation d'URL/historique possible.
        if ($request->wantsJson()) {
            return response()->json($posts);
        }

        return Inertia::render('MyRecipes/Index', [
            'posts' => $posts,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('MyRecipes/Create', [
            'categories' => Category::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
            'calories' => ['nullable', 'integer', 'min:0', 'max:20000'],
            'calories_unit' => ['nullable', 'in:g,ml'],
            'category_ids' => ['array'],
            'category_ids.*' => ['exists:categories,id'],
            'image' => ['nullable', 'image', 'max:4096'],
            'ingredients' => ['array'],
            'ingredients.*.name' => ['required_with:ingredients', 'string', 'max:150'],
            'ingredients.*.amount' => ['nullable', 'string', 'max:30'],
            'ingredients.*.unit' => ['nullable', 'string', 'max:30'],
        ]);

        // Chacun publie librement ses propres recettes — pas de validation éditoriale
        // obligatoire ici (contrairement à Admin\PostController qui gère la modération
        // globale via la permission publish-posts).
        $publish = $request->boolean('publish');

        $post = Post::create([
            'title' => $data['title'],
            'content' => $data['content'] ?? null,
            'calories' => $data['calories'] ?? null,
            'calories_unit' => $data['calories_unit'] ?? null,
            'calories_is_auto' => false,
            'user_id' => Auth::id(),
            'status' => $publish ? 'published' : 'draft',
            'published_at' => $publish ? now() : null,
        ]);

        if (! empty($data['category_ids'])) {
            $post->categories()->sync($data['category_ids']);
        }

        if ($request->hasFile('image')) {
            $post->update(['image_path' => $request->file('image')->store('posts', 'public')]);
        }

        foreach ($data['ingredients'] ?? [] as $i => $ingredient) {
            PostIngredient::create([
                'post_id' => $post->id,
                'name' => $ingredient['name'],
                'amount' => $ingredient['amount'] ?? null,
                'unit' => $ingredient['unit'] ?? null,
                'order' => $i,
            ]);
        }

        // 🔒 Fonctionnalité "estimation automatique des calories" masquée temporairement
        // (code conservé pour réactivation future — voir aussi Edit.vue, Create.vue,
        // AdminLayout.vue "Ingrédients"). Décommenter pour réactiver :
        //
        // if (empty($data['calories']) && ! empty($data['ingredients'])) {
        //     $this->applyCalorieEstimate($post, $data['ingredients']);
        // }

        Notification::send(User::role('admin')->get(), new NewRecipeCreatedNotification($post));

        return redirect()->route('my-recipes.index')
            ->with('success', 'Recette créée.');
    }

    public function edit(Post $post): Response
    {
        $this->authorizeOwner($post);

        return Inertia::render('MyRecipes/Edit', [
            'post' => $post->load(['categories:id,name', 'ingredients', 'steps.images']),
            'categories' => Category::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(Request $request, Post $post)
    {
        $this->authorizeOwner($post);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
            'calories' => ['nullable', 'integer', 'min:0', 'max:20000'],
            'calories_unit' => ['nullable', 'in:g,ml'],
            'category_ids' => ['array'],
            'category_ids.*' => ['exists:categories,id'],
            'image' => ['nullable', 'image', 'max:4096'],
            'ingredients' => ['array'],
            'ingredients.*.name' => ['required_with:ingredients', 'string', 'max:150'],
            'ingredients.*.amount' => ['nullable', 'string', 'max:30'],
            'ingredients.*.unit' => ['nullable', 'string', 'max:30'],
        ]);

        $post->fill([
            'title' => $data['title'],
            'content' => $data['content'] ?? null,
            'calories' => $data['calories'] ?? null,
            'calories_unit' => $data['calories_unit'] ?? null,
            'calories_is_auto' => false,
            'calories_breakdown' => null,
        ]);

        $post->save();
        $post->categories()->sync($data['category_ids'] ?? []);

        if ($request->hasFile('image')) {
            if ($post->image_path) {
                Storage::disk('public')->delete($post->image_path);
            }
            $post->update(['image_path' => $request->file('image')->store('posts', 'public')]);
        }

        // Ingrédients : simple texte, pas de fichiers → on remplace tout à chaque sauvegarde.
        $post->ingredients()->delete();
        foreach ($data['ingredients'] ?? [] as $i => $ingredient) {
            PostIngredient::create([
                'post_id' => $post->id,
                'name' => $ingredient['name'],
                'amount' => $ingredient['amount'] ?? null,
                'unit' => $ingredient['unit'] ?? null,
                'order' => $i,
            ]);
        }

        // 🔒 Fonctionnalité "estimation automatique des calories" masquée temporairement
        // (voir store() pour le détail). Décommenter pour réactiver :
        //
        // if (empty($data['calories']) && ! empty($data['ingredients'])) {
        //     $this->applyCalorieEstimate($post, $data['ingredients']);
        // }

        return redirect()->route('my-recipes.index')->with('success', 'Recette mise à jour.');
    }

    public function toggleStatus(Request $request, Post $post)
    {
        $this->authorizeOwner($post);

        $data = $request->validate([
            'status' => ['required', 'in:draft,published'],
        ]);

        $post->status = $data['status'];
        $post->published_at = $data['status'] === 'published' ? ($post->published_at ?? now()) : null;
        $post->save();

        return back()->with(
            'success',
            $data['status'] === 'published' ? 'Recette publiée.' : 'Recette remise en brouillon.'
        );
    }

    public function destroy(Post $post)
    {
        $this->authorizeOwner($post);

        foreach ($post->steps as $step) {
            $this->deleteStepFiles($step);
        }
        if ($post->image_path) {
            Storage::disk('public')->delete($post->image_path);
        }

        $post->delete();

        return redirect()->route('my-recipes.index')->with('success', 'Recette supprimée.');
    }

    // -----------------------------------------------------------------
    // Étapes — instant-CRUD, comme les sections de page dans PageController
    // -----------------------------------------------------------------

    public function storeStep(Request $request, Post $post)
    {
        $this->authorizeOwner($post);

        $data = $this->validateStep($request);

        $step = $post->steps()->create([
            'instruction' => $data['instruction'],
            'order' => $post->steps()->max('order') + 1,
        ]);

        if ($request->hasFile('video')) {
            $step->update(['video_path' => $request->file('video')->store('recipe-videos', 'public')]);
        }

        foreach ($request->file('images', []) as $i => $file) {
            PostStepImage::create([
                'post_step_id' => $step->id,
                'path' => $file->store('recipe-steps', 'public'),
                'order' => $i,
            ]);
        }

        return back()->with('success', 'Étape ajoutée.');
    }

    public function updateStep(Request $request, Post $post, PostStep $step)
    {
        $this->authorizeOwner($post);
        abort_unless($step->post_id === $post->id, 404);

        $data = $this->validateStep($request);
        $step->update(['instruction' => $data['instruction']]);

        if ($request->hasFile('video')) {
            if ($step->video_path) {
                Storage::disk('public')->delete($step->video_path);
            }
            $step->update(['video_path' => $request->file('video')->store('recipe-videos', 'public')]);
        }

        $existingCount = $step->images()->count();
        foreach ($request->file('images', []) as $i => $file) {
            PostStepImage::create([
                'post_step_id' => $step->id,
                'path' => $file->store('recipe-steps', 'public'),
                'order' => $existingCount + $i,
            ]);
        }

        return back()->with('success', 'Étape mise à jour.');
    }

    public function reorderSteps(Request $request, Post $post)
    {
        $this->authorizeOwner($post);

        $data = $request->validate([
            'order' => ['required', 'array'],
            'order.*' => ['integer', 'exists:post_steps,id'],
        ]);

        foreach ($data['order'] as $index => $stepId) {
            PostStep::where('id', $stepId)->where('post_id', $post->id)->update(['order' => $index]);
        }

        return back();
    }

    public function destroyStep(Post $post, PostStep $step)
    {
        $this->authorizeOwner($post);
        abort_unless($step->post_id === $post->id, 404);

        $this->deleteStepFiles($step);
        $step->delete();

        return back()->with('success', 'Étape supprimée.');
    }

    public function destroyStepImage(Post $post, PostStep $step, PostStepImage $image)
    {
        $this->authorizeOwner($post);
        abort_unless($step->post_id === $post->id && $image->post_step_id === $step->id, 404);

        Storage::disk('public')->delete($image->path);
        $image->delete();

        return back()->with('success', 'Image supprimée.');
    }

    public function destroyStepVideo(Post $post, PostStep $step)
    {
        $this->authorizeOwner($post);
        abort_unless($step->post_id === $post->id, 404);

        if ($step->video_path) {
            Storage::disk('public')->delete($step->video_path);
            $step->update(['video_path' => null]);
        }

        return back()->with('success', 'Vidéo supprimée.');
    }

    // -----------------------------------------------------------------

    private function validateStep(Request $request): array
    {
        return $request->validate([
            'instruction' => ['required', 'string', 'max:2000'],
            'images' => ['array'],
            'images.*' => ['image', 'max:4096'],
            'video' => ['nullable', 'file', 'mimetypes:video/mp4,video/quicktime,video/webm', 'max:20480'],
        ]);
    }

    private function deleteStepFiles(PostStep $step): void
    {
        foreach ($step->images as $image) {
            Storage::disk('public')->delete($image->path);
        }
        if ($step->video_path) {
            Storage::disk('public')->delete($step->video_path);
        }
    }

    /**
     * Route dédiée pour redéclencher l'estimation manuellement depuis l'écran d'édition
     * (utile si les ingrédients ont changé sans repasser par le formulaire principal).
     */
    public function estimateCalories(Post $post)
    {
        $this->authorizeOwner($post);

        $ingredients = $post->ingredients()->get(['name', 'amount', 'unit'])->toArray();
        $applied = $this->applyCalorieEstimate($post, $ingredients);

        return back()->with(
            $applied ? 'success' : 'error',
            $applied
                ? 'Calories estimées à partir des ingrédients.'
                : "Estimation impossible — trop peu d'ingrédients reconnus avec une quantité exploitable."
        );
    }

    /**
     * @param  array<int, array{amount?: ?string, unit?: ?string, name: string}>  $ingredients
     */
    private function applyCalorieEstimate(Post $post, array $ingredients): bool
    {
        $estimate = $this->calorieEstimator->estimate($ingredients);

        if (! $estimate) {
            return false;
        }

        $post->update([
            'calories' => $estimate['calories'],
            'calories_unit' => $estimate['unit'],
            'calories_is_auto' => true,
            'calories_breakdown' => $estimate['breakdown'],
        ]);

        return true;
    }

    private function authorizeOwner(Post $post): void
    {
        abort_unless($post->user_id === Auth::id(), 403);
    }
}
