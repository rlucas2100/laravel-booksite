<?php

use App\Enums\EntryType;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\CommunityController as AdminCommunityController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\EntryController as AdminEntryController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\CatalogueController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\EntryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReviewController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Open to everyone
|--------------------------------------------------------------------------
*/

Route::get('/', HomeController::class)->name('home');
Route::get('/browse', [CatalogueController::class, 'index'])->name('browse');
Route::get('/categories/{category}', [CatalogueController::class, 'category'])->name('categories.show');

// Covers and files are kept out of the public folder and handed out by a controller.
Route::get('/media/{entry}/cover', [MediaController::class, 'cover'])->name('media.cover');
Route::get('/media/{entry}/file', [MediaController::class, 'file'])->name('media.file');

/*
|--------------------------------------------------------------------------
| Members
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    Route::get('/upload', [EntryController::class, 'create'])->name('entries.create');
    Route::post('/upload', [EntryController::class, 'store'])->middleware('throttle:uploads')->name('entries.store');
    Route::get('/my-uploads', [EntryController::class, 'mine'])->name('entries.mine');
    Route::get('/entries/{entry}/edit', [EntryController::class, 'edit'])->name('entries.edit');
    Route::put('/entries/{entry}', [EntryController::class, 'update'])->middleware('throttle:uploads')->name('entries.update');
    Route::delete('/entries/{entry}', [EntryController::class, 'destroy'])->name('entries.destroy');

    Route::post('/entries/{entry}/reviews', [ReviewController::class, 'store'])->middleware('throttle:community')->name('reviews.store');
    Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');

    Route::post('/entries/{entry}/comments', [CommentController::class, 'store'])->middleware('throttle:community')->name('comments.store');
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'password'])->name('profile.password');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| Admins
|--------------------------------------------------------------------------
| "admin" lets admins and the owner in; each page then checks its own
| permission (manage-users, manage-entries...) through a policy.
*/

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', AdminDashboardController::class)->name('dashboard');
    Route::get('/catalogue', AdminEntryController::class)->name('entries');

    Route::get('/community', [AdminCommunityController::class, 'index'])->name('community');

    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::get('/users/{user}/edit', [AdminUserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');

    Route::get('/categories', [AdminCategoryController::class, 'index'])->name('categories.index');
    Route::post('/categories', [AdminCategoryController::class, 'store'])->name('categories.store');
    Route::put('/categories/{category}', [AdminCategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{category}', [AdminCategoryController::class, 'destroy'])->name('categories.destroy');
});

require __DIR__.'/auth.php';

/*
|--------------------------------------------------------------------------
| The shelves
|--------------------------------------------------------------------------
| /books, /videos, /audio, /blog and one entry on a shelf: /books/dracula.
| Kept last so they never swallow another address.
*/

Route::get('/{shelf}', [CatalogueController::class, 'shelf'])
    ->whereIn('shelf', EntryType::segments())->name('shelf');

Route::get('/{shelf}/{entry}', [CatalogueController::class, 'show'])
    ->whereIn('shelf', EntryType::segments())->name('entries.show');

// Anything else: the shop's own "page not found".
Route::fallback(fn () => abort(404));
