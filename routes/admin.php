<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\HomepageController;
use App\Http\Controllers\Admin\IngredientNutritionController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\PostController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Zone Admin — /admin
|--------------------------------------------------------------------------
| Toutes les routes exigent : authentifié + rôle "admin" exclusivement.
| Les permissions internes (manage-posts, manage-categories...) restent en place
| pour affiner ce que fait un admin, mais editor/contributor n'accèdent plus du
| tout à /admin — voir MyRecipesController pour leur gestion de recettes.
*/

Route::middleware(['auth', 'verified', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        // Posts / Recettes — manage-own-posts suffit pour accéder au listing,
        // le contrôleur restreint ensuite ce que chacun peut voir/éditer.
        Route::middleware('permission:manage-posts|manage-own-posts')->group(function () {
            Route::get('/posts', [PostController::class, 'index'])->name('posts.index');
            Route::get('/posts/create', [PostController::class, 'create'])->name('posts.create');
            Route::post('/posts', [PostController::class, 'store'])->name('posts.store');
            Route::get('/posts/{post}/edit', [PostController::class, 'edit'])->name('posts.edit');
            Route::put('/posts/{post}', [PostController::class, 'update'])->name('posts.update');
            Route::delete('/posts/{post}', [PostController::class, 'destroy'])->name('posts.destroy');
        });

        // Catégories
        Route::middleware('permission:manage-categories')->group(function () {
            Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
            Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
            Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
            Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
        });

        // Pages + sections
        Route::middleware('permission:manage-pages')->group(function () {
            Route::get('/pages', [PageController::class, 'index'])->name('pages.index');
            Route::get('/pages/create', [PageController::class, 'create'])->name('pages.create');
            Route::post('/pages', [PageController::class, 'store'])->name('pages.store');
            Route::get('/pages/{page}/edit', [PageController::class, 'edit'])->name('pages.edit');
            Route::put('/pages/{page}', [PageController::class, 'update'])->name('pages.update');
            Route::delete('/pages/{page}', [PageController::class, 'destroy'])->name('pages.destroy');

            Route::post('/pages/{page}/sections', [PageController::class, 'storeSection'])->name('pages.sections.store');
            Route::put('/pages/{page}/sections/{section}', [PageController::class, 'updateSection'])->name('pages.sections.update');
            Route::delete('/pages/{page}/sections/{section}', [PageController::class, 'destroySection'])->name('pages.sections.destroy');
            Route::post('/pages/{page}/sections/reorder', [PageController::class, 'reorderSections'])->name('pages.sections.reorder');
        });

        // Utilisateurs & rôles
        Route::middleware('permission:manage-users')->group(function () {
            Route::get('/users', [UserController::class, 'index'])->name('users.index');
            Route::put('/users/{user}/role', [UserController::class, 'updateRole'])->name('users.role');
            Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
        });

        // Ingrédients & valeurs nutritionnelles (base de référence pour l'estimation calorique)
        Route::middleware('permission:manage-nutrition')->group(function () {
            Route::get('/ingredients', [IngredientNutritionController::class, 'index'])->name('ingredients.index');
            Route::post('/ingredients', [IngredientNutritionController::class, 'store'])->name('ingredients.store');
            Route::put('/ingredients/{ingredient}', [IngredientNutritionController::class, 'update'])->name('ingredients.update');
            Route::delete('/ingredients/{ingredient}', [IngredientNutritionController::class, 'destroy'])->name('ingredients.destroy');
        });

        // Paramètres du site
        Route::middleware('permission:manage-settings')->group(function () {
            Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
            Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');

            Route::get('/homepage', [HomepageController::class, 'index'])->name('homepage.index');
            Route::post('/homepage', [HomepageController::class, 'update'])->name('homepage.update');
            Route::delete('/homepage/image', [HomepageController::class, 'destroyImage'])->name('homepage.image.destroy');
        });
    });
