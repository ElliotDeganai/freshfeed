<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Post;
use App\Models\PostIngredient;
use App\Models\PostStep;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FreshFeedDemoRecipesSeeder extends Seeder
{
    /**
     * Peuple l'app avec des catégories et 10 recettes de démo, réparties entre
     * les comptes de test (voir FreshFeedTestUsersSeeder) — à lancer APRÈS lui.
     * Chaque recette a des ingrédients, des étapes, et une image de couverture
     * générée en SVG à la volée (pas de dépendance à des fichiers externes).
     */
    public function run(): void
    {
        $categories = $this->seedCategories();
        $authors = $this->resolveAuthors();

        foreach ($this->recipes() as $i => $data) {
            $author = $authors[$i % count($authors)];

            $imagePath = $this->storeSvgIllustration($data['svg'], $data['bg'], $data['fg']);

            $post = Post::updateOrCreate(
                ['title' => $data['title'], 'user_id' => $author->id],
                [
                    'content' => $data['description'],
                    'calories' => $data['calories'],
                    'calories_unit' => $data['calories_unit'],
                    'image_path' => $imagePath,
                    'status' => $data['status'],
                    'published_at' => $data['status'] === 'published' ? now()->subDays(random_int(0, 20)) : null,
                ]
            );

            $categoryIds = collect($data['categories'])
                ->map(fn ($name) => $categories[$name]->id)
                ->all();
            $post->categories()->sync($categoryIds);

            $post->ingredients()->delete();
            foreach ($data['ingredients'] as $order => $ingredient) {
                PostIngredient::create([
                    'post_id' => $post->id,
                    'amount' => $ingredient[0],
                    'unit' => $ingredient[1],
                    'name' => $ingredient[2],
                    'order' => $order,
                ]);
            }

            $post->steps()->delete();
            foreach ($data['steps'] as $order => $instruction) {
                PostStep::create([
                    'post_id' => $post->id,
                    'instruction' => $instruction,
                    'order' => $order,
                ]);
            }
        }

        $this->command->info('10 recettes de démo créées avec catégories, ingrédients, étapes et illustrations SVG.');
    }

    /**
     * @return array<string, Category>
     */
    private function seedCategories(): array
    {
        $names = [
            'Healthy', 'Rapide', 'Vegan', 'Comfort food',
            'Petit-déjeuner', 'Végétarien', 'Sans gluten', 'Dessert',
        ];

        $categories = [];
        foreach ($names as $name) {
            $categories[$name] = Category::firstOrCreate(
                ['name' => $name],
                ['slug' => Str::slug($name)]
            );
        }

        return $categories;
    }

    /**
     * @return array<int, User>
     */
    private function resolveAuthors(): array
    {
        $emails = [
            'admin.test@freshfeed.local',
            'editor.test@freshfeed.local',
            'contributor.test@freshfeed.local',
            'admin@freshfeed.local',
        ];

        $authors = User::whereIn('email', $emails)->get()->keyBy('email');

        // Repli si les seeders de comptes de test n'ont pas encore tourné :
        // on utilise le premier utilisateur existant plutôt que d'échouer.
        if ($authors->isEmpty()) {
            $fallback = User::first();
            abort_unless($fallback, 500, 'Aucun utilisateur en base — lance FreshFeedTestUsersSeeder avant celui-ci.');

            return [$fallback];
        }

        return collect($emails)->map(fn ($e) => $authors->get($e))->filter()->values()->all();
    }

    private function storeSvgIllustration(string $svgBody, string $bg, string $fg): string
    {
        $svg = <<<SVG
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200">
            <rect width="200" height="200" fill="{$bg}"/>
            <g transform="translate(28 28) scale(2)">{$svgBody}</g>
        </svg>
        SVG;

        $filename = 'posts/seed-' . Str::random(12) . '.svg';
        Storage::disk('public')->put($filename, $svg);

        return $filename;
    }

    private function recipes(): array
    {
        // Motifs SVG réutilisés du style FreshFeed (viewBox interne 0 0 72 72,
        // repositionnés/redimensionnés dans storeSvgIllustration).
        $avocado = '<ellipse cx="36" cy="42" rx="26" ry="32" fill="#B7E4D4"/><ellipse cx="36" cy="42" rx="18" ry="24" fill="#0F6E56"/><circle cx="36" cy="46" r="10" fill="#854F0B"/>';
        $bowl = '<path d="M10 30 Q45 30 80 30 L74 55 Q45 65 16 55 Z" fill="#fff" opacity=".9"/><path d="M28 30 Q30 14 24 6" fill="none" stroke="#0F6E56" stroke-width="4" stroke-linecap="round"/><path d="M40 30 Q44 10 38 2" fill="none" stroke="#1D9E75" stroke-width="4" stroke-linecap="round"/><path d="M52 30 Q50 14 56 6" fill="none" stroke="#0F6E56" stroke-width="4" stroke-linecap="round"/>';
        $citrus = '<circle cx="35" cy="35" r="30" fill="#fff" opacity=".9"/><g stroke="#E3B23C" stroke-width="2"><line x1="35" y1="35" x2="35" y2="12"/><line x1="35" y1="35" x2="53" y2="22"/><line x1="35" y1="35" x2="53" y2="48"/><line x1="35" y1="35" x2="35" y2="58"/><line x1="35" y1="35" x2="17" y2="48"/><line x1="35" y1="35" x2="17" y2="22"/></g>';
        $chili = '<path d="M28 10C20 8 16 14 18 20C10 26 8 46 20 62C28 74 42 74 48 62C56 48 50 26 38 20C42 12 34 8 28 10Z" fill="#fff" opacity=".9"/><path d="M26 12Q22 6 14 6" fill="none" stroke="#146C4E" stroke-width="3" stroke-linecap="round"/>';
        $herb = '<line x1="30" y1="85" x2="30" y2="15" stroke="#fff" stroke-width="3" stroke-linecap="round" opacity=".9"/><ellipse cx="18" cy="30" rx="10" ry="5" fill="#fff" opacity=".9" transform="rotate(-35 18 30)"/><ellipse cx="42" cy="30" rx="10" ry="5" fill="#fff" opacity=".7" transform="rotate(35 42 30)"/><ellipse cx="16" cy="50" rx="10" ry="5" fill="#fff" opacity=".7" transform="rotate(-35 16 50)"/><ellipse cx="44" cy="50" rx="10" ry="5" fill="#fff" opacity=".9" transform="rotate(35 44 50)"/>';
        $tomato = '<circle cx="35" cy="38" r="26" fill="#fff" opacity=".9"/><path d="M25 20Q35 12 45 20Q38 16 35 22Q32 16 25 20Z" fill="#146C4E"/>';
        $pasta = '<path d="M14 50c8-30 44-30 44 0" fill="none" stroke="#fff" stroke-width="6" stroke-linecap="round" opacity=".9"/><path d="M22 50c4-18 24-18 28 0" fill="none" stroke="#fff" stroke-width="5" stroke-linecap="round" opacity=".7"/><circle cx="20" cy="50" r="4" fill="#993C1D"/><circle cx="50" cy="50" r="4" fill="#993C1D"/>';
        $smoothie = '<path d="M22 14h28l-4 46a6 6 0 0 1-6 5H32a6 6 0 0 1-6-5Z" fill="#fff" opacity=".9"/><rect x="20" y="10" width="32" height="8" rx="3" fill="#fff" opacity=".9"/><line x1="46" y1="4" x2="38" y2="24" stroke="#0F6E56" stroke-width="3" stroke-linecap="round"/>';
        $pancake = '<ellipse cx="36" cy="52" rx="24" ry="7" fill="#fff" opacity=".7"/><ellipse cx="36" cy="42" rx="22" ry="7" fill="#fff" opacity=".8"/><ellipse cx="36" cy="32" rx="20" ry="7" fill="#fff" opacity=".95"/><path d="M18 30q18 14 36 0" fill="none" stroke="#854F0B" stroke-width="3" stroke-linecap="round"/>';
        $wrap = '<path d="M14 26c0-10 44-10 44 0v20c0 14-44 14-44 0Z" fill="#fff" opacity=".9"/><line x1="18" y1="30" x2="54" y2="30" stroke="#0F6E56" stroke-width="2"/><line x1="18" y1="38" x2="54" y2="38" stroke="#993C1D" stroke-width="2"/><line x1="18" y1="46" x2="54" y2="46" stroke="#1D9E75" stroke-width="2"/>';
        $galette = '<circle cx="36" cy="36" r="28" fill="#fff" opacity=".9"/><path d="M36 8a28 28 0 0 1 24 42Z" fill="#F4D06F" opacity=".6"/><circle cx="30" cy="30" r="4" fill="#993C1D"/><circle cx="44" cy="40" r="3" fill="#993C1D"/>';
        $cake = '<rect x="14" y="40" width="44" height="20" rx="3" fill="#fff" opacity=".95"/><rect x="14" y="26" width="44" height="16" rx="3" fill="#fff" opacity=".8"/><rect x="30" y="10" width="4" height="18" fill="#993C1D"/><circle cx="32" cy="8" r="3" fill="#F4D06F"/>';

                $galette = '<circle cx="36" cy="36" r="28" fill="#fff" opacity=".9"/><path d="M36 8a28 28 0 0 1 24 42Z" fill="#F4D06F" opacity=".6"/><circle cx="30" cy="30" r="4" fill="#993C1D"/><circle cx="44" cy="40" r="3" fill="#993C1D"/>';
        $cake = '<rect x="14" y="40" width="44" height="20" rx="3" fill="#fff" opacity=".95"/><rect x="14" y="26" width="44" height="16" rx="3" fill="#fff" opacity=".8"/><rect x="30" y="10" width="4" height="18" fill="#993C1D"/><circle cx="32" cy="8" r="3" fill="#F4D06F"/>';

return [
            [
                'title' => 'Bowl de poulet healthy',
                'description' => "<p>Un bowl complet et équilibré, parfait pour un déjeuner rapide sans sacrifier les saveurs.</p>",
                'calories' => 420, 'calories_unit' => 'g',
                'categories' => ['Healthy', 'Rapide'],
                'status' => 'published',
                'svg' => $bowl, 'bg' => '#E1F5EE', 'fg' => '#0F6E56',
                'ingredients' => [
                    ['150', 'g', 'blanc de poulet'],
                    ['80', 'g', 'riz complet'],
                    ['1', '', 'avocat'],
                    ['50', 'g', 'edamame'],
                    ['1', 'c. à soupe', 'sauce soja'],
                ],
                'steps' => [
                    "Cuire le riz complet selon les instructions du paquet.",
                    "Faire griller le blanc de poulet coupé en lamelles avec un filet d'huile d'olive.",
                    "Disposer le riz, le poulet, l'avocat tranché et les edamame dans un bol.",
                    "Arroser de sauce soja et servir aussitôt.",
                ],
            ],
            [
                'title' => 'Pâtes à la carbonara traditionnelle',
                'description' => "<p>La vraie recette italienne, sans crème, juste des œufs, du parmesan et du guanciale.</p>",
                'calories' => 580, 'calories_unit' => 'g',
                'categories' => ['Comfort food'],
                'status' => 'published',
                'svg' => $pasta, 'bg' => '#FAEEDA', 'fg' => '#854F0B',
                'ingredients' => [
                    ['200', 'g', 'spaghetti'],
                    ['100', 'g', 'guanciale'],
                    ['2', '', 'œufs entiers'],
                    ['50', 'g', 'parmesan râpé'],
                    ['1', 'c. à café', 'poivre noir'],
                ],
                'steps' => [
                    "Faire cuire les spaghetti dans une grande quantité d'eau salée.",
                    "Pendant ce temps, faire revenir le guanciale coupé en lardons jusqu'à ce qu'il soit doré.",
                    "Battre les œufs avec le parmesan et beaucoup de poivre.",
                    "Égoutter les pâtes en gardant un peu d'eau de cuisson, mélanger hors du feu avec le guanciale puis les œufs.",
                ],
            ],
            [
                'title' => 'Smoothie bowl aux fruits rouges',
                'description' => "<p>Un petit-déjeuner coloré et vitaminé, prêt en 5 minutes.</p>",
                'calories' => 180, 'calories_unit' => 'ml',
                'categories' => ['Vegan', 'Petit-déjeuner'],
                'status' => 'published',
                'svg' => $smoothie, 'bg' => '#FBEAF0', 'fg' => '#993556',
                'ingredients' => [
                    ['200', 'g', 'fruits rouges surgelés'],
                    ['1', '', 'banane'],
                    ['100', 'ml', "lait d'amande"],
                    ['1', 'c. à soupe', 'graines de chia'],
                    ['1', 'poignée', 'granola'],
                ],
                'steps' => [
                    "Mixer les fruits rouges surgelés, la banane et le lait d'amande jusqu'à obtenir une texture épaisse.",
                    "Verser dans un bol.",
                    "Parsemer de graines de chia et de granola avant de servir.",
                ],
            ],
            [
                'title' => 'Salade César revisitée',
                'description' => "<p>Une version plus légère de la classique, avec une sauce au yaourt.</p>",
                'calories' => 310, 'calories_unit' => 'g',
                'categories' => ['Healthy'],
                'status' => 'published',
                'svg' => $bowl, 'bg' => '#EEEDFE', 'fg' => '#534AB7',
                'ingredients' => [
                    ['1', '', 'laitue romaine'],
                    ['100', 'g', 'poulet grillé'],
                    ['30', 'g', 'parmesan'],
                    ['2', 'c. à soupe', 'yaourt grec'],
                    ['1', 'poignée', 'croûtons'],
                ],
                'steps' => [
                    "Laver et couper la laitue romaine.",
                    "Mélanger le yaourt grec avec un filet de citron et de l'ail pour la sauce.",
                    "Disposer la laitue, le poulet grillé, les croûtons et le parmesan.",
                    "Napper de sauce juste avant de servir.",
                ],
            ],
            [
                'title' => 'Tacos au poulet épicé',
                'description' => "<p>Des tacos généreux et rapides à préparer pour un dîner convivial.</p>",
                'calories' => 460, 'calories_unit' => 'g',
                'categories' => ['Rapide'],
                'status' => 'published',
                'svg' => $chili, 'bg' => '#FAECE7', 'fg' => '#993C1D',
                'ingredients' => [
                    ['300', 'g', 'blanc de poulet'],
                    ['1', 'sachet', 'épices tex-mex'],
                    ['6', '', 'tortillas'],
                    ['1', '', 'avocat'],
                    ['100', 'g', 'chou rouge émincé'],
                ],
                'steps' => [
                    "Couper le poulet en lanières et le mariner avec les épices tex-mex.",
                    "Faire revenir le poulet à feu vif jusqu'à ce qu'il soit bien coloré.",
                    "Réchauffer les tortillas quelques secondes à la poêle.",
                    "Garnir de poulet, avocat et chou rouge, plier et déguster.",
                ],
            ],
            [
                'title' => 'Soupe miso maison',
                'description' => "<p>Réconfortante et rapide, cette soupe miso est parfaite en entrée ou en repas léger.</p>",
                'calories' => 90, 'calories_unit' => 'ml',
                'categories' => ['Végétarien', 'Rapide'],
                'status' => 'published',
                'svg' => $herb, 'bg' => '#E6F1FB', 'fg' => '#0C447C',
                'ingredients' => [
                    ['1', 'litre', 'bouillon dashi'],
                    ['3', 'c. à soupe', 'pâte miso'],
                    ['200', 'g', 'tofu soyeux'],
                    ['2', '', 'oignons verts'],
                    ['1', 'feuille', 'algue wakame'],
                ],
                'steps' => [
                    "Faire chauffer le bouillon dashi sans le porter à ébullition.",
                    "Diluer la pâte miso dans une louche de bouillon chaud puis reverser dans la casserole.",
                    "Ajouter le tofu coupé en dés et l'algue wakame réhydratée.",
                    "Parsemer d'oignons verts émincés avant de servir.",
                ],
            ],
            [
                'title' => 'Pancakes protéinés à la banane',
                'description' => "<p>Le petit-déjeuner idéal après une séance de sport, riche en protéines.</p>",
                'calories' => 320, 'calories_unit' => 'g',
                'categories' => ['Petit-déjeuner', 'Healthy'],
                'status' => 'published',
                'svg' => $pancake, 'bg' => '#FAEEDA', 'fg' => '#854F0B',
                'ingredients' => [
                    ['2', '', 'bananes mûres'],
                    ['3', '', 'œufs'],
                    ['30', 'g', "flocons d'avoine"],
                    ['1', 'dose', 'protéine en poudre vanille'],
                    ['1', 'c. à café', 'levure chimique'],
                ],
                'steps' => [
                    "Écraser les bananes à la fourchette.",
                    "Mixer avec les œufs, les flocons d'avoine, la protéine et la levure.",
                    "Faire cuire des petites louches de pâte dans une poêle légèrement huilée, 2 minutes de chaque côté.",
                    "Servir avec des fruits frais.",
                ],
            ],
            [
                'title' => 'Curry de légumes au lait de coco',
                'description' => "<p>Un curry végétalien parfumé, sans gluten, qui se prépare en une seule casserole.</p>",
                'calories' => 260, 'calories_unit' => 'g',
                'categories' => ['Vegan', 'Sans gluten'],
                'status' => 'draft',
                'svg' => $avocado, 'bg' => '#E1F5EE', 'fg' => '#0F6E56',
                'ingredients' => [
                    ['400', 'ml', 'lait de coco'],
                    ['1', '', 'patate douce'],
                    ['1', '', 'poivron rouge'],
                    ['150', 'g', 'pois chiches'],
                    ['2', 'c. à soupe', 'pâte de curry'],
                ],
                'steps' => [
                    "Faire revenir la pâte de curry dans un peu d'huile.",
                    "Ajouter la patate douce et le poivron coupés en dés, puis le lait de coco.",
                    "Laisser mijoter 20 minutes à couvert.",
                    "Ajouter les pois chiches en fin de cuisson et rectifier l'assaisonnement.",
                ],
            ],
            [
                'title' => 'Risotto aux champignons',
                'description' => "<p>Crémeux et réconfortant, ce risotto met en valeur les champignons de saison.</p>",
                'calories' => 510, 'calories_unit' => 'g',
                'categories' => ['Comfort food', 'Végétarien'],
                'status' => 'published',
                'svg' => $tomato, 'bg' => '#FAECE7', 'fg' => '#993C1D',
                'ingredients' => [
                    ['300', 'g', 'riz arborio'],
                    ['300', 'g', 'champignons de Paris'],
                    ['1', 'litre', 'bouillon de légumes'],
                    ['80', 'g', 'parmesan'],
                    ['1', 'verre', 'vin blanc sec'],
                ],
                'steps' => [
                    "Faire revenir les champignons émincés, réserver.",
                    "Faire nacrer le riz puis déglacer au vin blanc.",
                    "Ajouter le bouillon chaud louche par louche en remuant constamment.",
                    "En fin de cuisson, incorporer les champignons et le parmesan.",
                ],
            ],
            [
                'title' => 'Wrap avocat & houmous',
                'description' => "<p>Un wrap frais et croquant, prêt en moins de 10 minutes, idéal pour le midi.</p>",
                'calories' => 380, 'calories_unit' => 'g',
                'categories' => ['Rapide', 'Végétarien'],
                'status' => 'published',
                'svg' => $wrap, 'bg' => '#E1F5EE', 'fg' => '#0F6E56',
                'ingredients' => [
                    ['2', '', 'grandes tortillas'],
                    ['4', 'c. à soupe', 'houmous'],
                    ['1', '', 'avocat'],
                    ['100', 'g', 'carottes râpées'],
                    ['1', 'poignée', "pousses d'épinards"],
                ],
                'steps' => [
                    "Tartiner généreusement les tortillas de houmous.",
                    "Répartir l'avocat tranché, les carottes râpées et les pousses d'épinards.",
                    "Rouler fermement les tortillas.",
                    "Couper en deux et servir aussitôt.",
                ],
            ],
            [
                'title' => 'Galette bretonne au sarrasin',
                'description' => "<p>La vraie galette complète, croustillante sur les bords, fondante au centre.</p>",
                'calories' => 340, 'calories_unit' => 'g',
                'categories' => ['Rapide', 'Végétarien'],
                'status' => 'published',
                'svg' => $galette, 'bg' => '#FAEEDA', 'fg' => '#854F0B',
                'ingredients' => [
                    ['250', 'g', 'farine de sarrasin'],
                    ['50', 'cl', 'eau'],
                    ['1', 'c. à café', 'sel'],
                    ['2', '', 'oeufs'],
                    ['100', 'g', 'jambon et fromage rape'],
                ],
                'steps' => [
                    "Mélanger la farine de sarrasin, l'eau et le sel, laisser reposer la pâte 1h.",
                    "Cuire une fine couche de pâte sur une poêle bien chaude.",
                    "Casser un oeuf au centre, ajouter jambon et fromage.",
                    "Replier les quatre bords et laisser cuire jusqu'à ce que l'oeuf soit pris.",
                ],
            ],
            [
                'title' => 'Buddha bowl quinoa & legumes rôtis',
                'description' => "<p>Un bowl coloré et complet, riche en fibres et en protéines végétales.</p>",
                'calories' => 390, 'calories_unit' => 'g',
                'categories' => ['Vegan', 'Healthy'],
                'status' => 'published',
                'svg' => $bowl, 'bg' => '#FBEAF0', 'fg' => '#993556',
                'ingredients' => [
                    ['100', 'g', 'quinoa'],
                    ['1', '', 'patate douce'],
                    ['150', 'g', 'chou-fleur'],
                    ['100', 'g', 'pois chiches rôtis'],
                    ['2', 'c. à soupe', 'tahini'],
                ],
                'steps' => [
                    "Cuire le quinoa selon les instructions du paquet.",
                    "Rôtir la patate douce et le chou-fleur au four 25 minutes avec un filet d'huile.",
                    "Disposer tous les éléments dans un bol.",
                    "Napper de sauce tahini déténdue avec un peu d'eau et de citron.",
                ],
            ],
            [
                'title' => 'Saumon grille, sauce citron',
                'description' => "<p>Un plat simple et raffiné, prêt en 20 minutes chrono.</p>",
                'calories' => 340, 'calories_unit' => 'g',
                'categories' => ['Healthy', 'Sans gluten'],
                'status' => 'published',
                'svg' => $citrus, 'bg' => '#E6F1FB', 'fg' => '#0C447C',
                'ingredients' => [
                    ['2', 'pavés', 'saumon'],
                    ['1', '', 'citron'],
                    ['2', 'c. à soupe', "huile d'olive"],
                    ['1', 'botte', 'asperges vertes'],
                    ['1', '', "gousse d'ail"],
                ],
                'steps' => [
                    "Faire mariner le saumon dans l'huile d'olive, le jus de citron et l'ail écrasé.",
                    "Faire griller les asperges à la poêle quelques minutes.",
                    "Cuire le saumon 4 minutes de chaque côté.",
                    "Servir avec un filet de sauce citronnee et du zeste.",
                ],
            ],
            [
                'title' => 'Chili sin carne',
                'description' => "<p>Un chili végétalien généreux, aussi rassasiant que la version classique.</p>",
                'calories' => 310, 'calories_unit' => 'g',
                'categories' => ['Vegan', 'Comfort food'],
                'status' => 'published',
                'svg' => $chili, 'bg' => '#E1F5EE', 'fg' => '#0F6E56',
                'ingredients' => [
                    ['400', 'g', 'haricots rouges'],
                    ['400', 'g', 'tomates concassées'],
                    ['150', 'g', 'protéines de soja texturées'],
                    ['1', '', 'oignon'],
                    ['1', 'c. à soupe', 'paprika fumé'],
                ],
                'steps' => [
                    "Faire revenir l'oignon émincé puis ajouter les protéines de soja rehydratees.",
                    "Ajouter les tomates concassées, les haricots rouges et les épices.",
                    "Laisser mijoter 25 minutes à couvert.",
                    "Servir avec du riz ou des tortillas.",
                ],
            ],
            [
                'title' => 'Tarte aux pommes maison',
                'description' => "<p>Une tarte simple et généreuse, avec une pâte croustillante.</p>",
                'calories' => 290, 'calories_unit' => 'g',
                'categories' => ['Dessert'],
                'status' => 'published',
                'svg' => $cake, 'bg' => '#FAEEDA', 'fg' => '#854F0B',
                'ingredients' => [
                    ['1', '', 'pâte brisée'],
                    ['5', '', 'pommes'],
                    ['50', 'g', 'sucre roux'],
                    ['1', 'c. à café', 'cannelle'],
                    ['20', 'g', 'beurre'],
                ],
                'steps' => [
                    "Étaler la pâte brisée dans un moule à tarte.",
                    "Couper les pommes en fines tranches et les disposer en rosace.",
                    "Saupoudrer de sucre roux et de cannelle, parsemer de noisettes de beurre.",
                    "Cuire 35 minutes à 180°C jusqu'à ce que la pâte soit dorée.",
                ],
            ],
            [
                'title' => 'Ratatouille provençale',
                'description' => "<p>Le grand classique d'été, encore meilleur réchauffé le lendemain.</p>",
                'calories' => 140, 'calories_unit' => 'g',
                'categories' => ['Végétarien', 'Sans gluten'],
                'status' => 'published',
                'svg' => $tomato, 'bg' => '#EEEDFE', 'fg' => '#534AB7',
                'ingredients' => [
                    ['2', '', 'courgettes'],
                    ['2', '', 'aubergines'],
                    ['3', '', 'tomates'],
                    ['1', '', 'poivron'],
                    ['3', 'gousses', 'ail'],
                ],
                'steps' => [
                    "Couper tous les legumes en dés réguliers.",
                    "Faire revenir chaque legume séparément dans l'huile d'olive.",
                    "Réunir tous les legumes avec l'ail écrasé et laisser mijoter 30 minutes.",
                    "Assaisonner avec du thym et du basilic frais avant de servir.",
                ],
            ],
            [
                'title' => 'Poke bowl au thon',
                'description' => "<p>Frais, coloré et sans cuisson — l'inspiration hawaïenne dans votre cuisine.</p>",
                'calories' => 400, 'calories_unit' => 'g',
                'categories' => ['Healthy', 'Rapide'],
                'status' => 'published',
                'svg' => $bowl, 'bg' => '#FAECE7', 'fg' => '#993C1D',
                'ingredients' => [
                    ['200', 'g', 'thon frais qualité sashimi'],
                    ['150', 'g', 'riz à sushi'],
                    ['1', '', 'mangue'],
                    ['1', '', 'concombre'],
                    ['2', 'c. à soupe', 'sauce soja'],
                ],
                'steps' => [
                    "Cuire le riz à sushi et l'assaisonner de vinaigre de riz.",
                    "Couper le thon en cubes et le mariner dans la sauce soja.",
                    "Couper la mangue et le concombre en dés.",
                    "Dresser le riz en base et disposer tous les éléments par-dessus.",
                ],
            ],
            [
                'title' => 'Houmous maison et crudités',
                'description' => "<p>Un houmous crémeux en 10 minutes, parfait pour l'apéro ou une collation saine.</p>",
                'calories' => 180, 'calories_unit' => 'g',
                'categories' => ['Vegan', 'Rapide'],
                'status' => 'published',
                'svg' => $avocado, 'bg' => '#FBEAF0', 'fg' => '#993556',
                'ingredients' => [
                    ['400', 'g', 'pois chiches cuits'],
                    ['2', 'c. à soupe', 'tahini'],
                    ['1', '', 'citron'],
                    ['1', 'gousse', 'ail'],
                    ['3', 'c. à soupe', "huile d'olive"],
                ],
                'steps' => [
                    "Mixer les pois chiches avec le tahini, le jus de citron et l'ail.",
                    "Ajouter l'huile d'olive petit à petit jusqu'à obtenir une texture lisse.",
                    "Rectifier l'assaisonnement en sel et citron.",
                    "Servir avec des bâtonnets de legumes frais.",
                ],
            ],
            [
                'title' => 'Mousse au chocolat vegan',
                'description' => "<p>Le secret : de l'aquafaba à la place des œufs, pour une mousse tout aussi aérienne.</p>",
                'calories' => 220, 'calories_unit' => 'g',
                'categories' => ['Dessert', 'Vegan'],
                'status' => 'draft',
                'svg' => $cake, 'bg' => '#E1F5EE', 'fg' => '#0F6E56',
                'ingredients' => [
                    ['200', 'g', 'chocolat noir'],
                    ['160', 'ml', "aquafaba (eau de pois chiches)"],
                    ['30', 'g', 'sucre glace'],
                    ['1', 'pincée', 'sel'],
                ],
                'steps' => [
                    "Faire fondre le chocolat noir au bain-marie.",
                    "Monter l'aquafaba en neige ferme avec une pincée de sel, comme des blancs d'œufs.",
                    "Incorporer délicatement le sucre glace puis le chocolat fondu tiédi.",
                    "Répartir dans des ramequins et réfrigérer au moins 3 heures.",
                ],
            ],
            [
                'title' => 'Quiche aux legumes sans gluten',
                'description' => "<p>Une pâte à base de farine de riz, aussi croustillante que l'originale.</p>",
                'calories' => 270, 'calories_unit' => 'g',
                'categories' => ['Sans gluten', 'Végétarien'],
                'status' => 'published',
                'svg' => $pancake, 'bg' => '#FAEEDA', 'fg' => '#854F0B',
                'ingredients' => [
                    ['200', 'g', 'farine de riz'],
                    ['100', 'g', 'beurre'],
                    ['4', '', 'oeufs'],
                    ['20', 'cl', 'crème liquide'],
                    ['200', 'g', 'légumes de saison'],
                ],
                'steps' => [
                    "Préparer la pâte en mélangéant farine de riz, beurre et un peu d'eau froide.",
                    "Foncer un moule et précuire la pâte 10 minutes à blanc.",
                    "Battre les oeufs avec la crème, ajouter les legumes coupés en dés.",
                    "Verser sur la pâte et cuire 35 minutes à 180°C.",
                ],
            ],
        ];
    }
}
