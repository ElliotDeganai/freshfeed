<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExploreController;
use App\Http\Controllers\FeedController;
use Inertia\Inertia;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MyRecipesController;
use App\Http\Controllers\PostShowController;
use App\Http\Controllers\ProfileAvatarController;
use App\Http\Controllers\UserProfileController;
use App\Http\Controllers\PostRatingController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

/* Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
}); */

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/feed', [FeedController::class, 'index'])->name('feed');
    Route::get('/explore', [ExploreController::class, 'index'])->name('explore');
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/my-recipes', [MyRecipesController::class, 'index'])->name('my-recipes.index');
    Route::get('/my-recipes/create', [MyRecipesController::class, 'create'])->name('my-recipes.create');
    Route::post('/my-recipes', [MyRecipesController::class, 'store'])->name('my-recipes.store');
    Route::get('/my-recipes/{post}/edit', [MyRecipesController::class, 'edit'])->name('my-recipes.edit');
    Route::put('/my-recipes/{post}', [MyRecipesController::class, 'update'])->name('my-recipes.update');
    Route::put('/my-recipes/{post}/status', [MyRecipesController::class, 'toggleStatus'])->name('my-recipes.status');
    Route::post('/my-recipes/{post}/estimate-calories', [MyRecipesController::class, 'estimateCalories'])->name('my-recipes.estimate-calories');
    Route::delete('/my-recipes/{post}', [MyRecipesController::class, 'destroy'])->name('my-recipes.destroy');

    // Étapes — instant-CRUD (voir MyRecipesController)
    Route::post('/my-recipes/{post}/steps', [MyRecipesController::class, 'storeStep'])->name('my-recipes.steps.store');
    Route::put('/my-recipes/{post}/steps/{step}', [MyRecipesController::class, 'updateStep'])->name('my-recipes.steps.update');
    Route::delete('/my-recipes/{post}/steps/{step}', [MyRecipesController::class, 'destroyStep'])->name('my-recipes.steps.destroy');
    Route::post('/my-recipes/{post}/steps/reorder', [MyRecipesController::class, 'reorderSteps'])->name('my-recipes.steps.reorder');
    Route::delete('/my-recipes/{post}/steps/{step}/images/{image}', [MyRecipesController::class, 'destroyStepImage'])->name('my-recipes.steps.images.destroy');
    Route::delete('/my-recipes/{post}/steps/{step}/video', [MyRecipesController::class, 'destroyStepVideo'])->name('my-recipes.steps.video.destroy');
});

Route::get('/posts/{post}', [PostShowController::class, 'show'])->name('posts.show');

Route::middleware('auth')->group(function () {
    Route::post('/profile/avatar', [ProfileAvatarController::class, 'update'])->name('profile.avatar.update');
    Route::delete('/profile/avatar', [ProfileAvatarController::class, 'destroy'])->name('profile.avatar.destroy');
});

Route::get('/u/{user}', [UserProfileController::class, 'show'])->name('users.show');

Route::middleware('auth')->group(function () {
    Route::post('/posts/{post}/rating', [PostRatingController::class, 'store'])->name('posts.rating.store');
    Route::delete('/posts/{post}/rating', [PostRatingController::class, 'destroy'])->name('posts.rating.destroy');
});

require __DIR__.'/auth.php';
require __DIR__.'/admin.php';
