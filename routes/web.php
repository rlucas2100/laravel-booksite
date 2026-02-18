<?php

use App\Http\Controllers\BookController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\PodcastController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\UserController;
use App\Models\Book;
use Illuminate\Support\Facades\Route;

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

//Route::get('/', function () {
//  if (auth()->user()) {
//        auth()->user()->assignRole('admin');
//  }
//  return view('home');
//});


Route::get('/my-books', [BookController::class, 'myBooks'])->name('my-books');

//Route::get('test', [TestController::class, 'index'])->name('test');

// admin route

// route group for admin only sections
Route::group(['middleware' => ['can:manage-users']], function () {
    Route::resource('users', UserController::class);

});

Route::resource('comments', \App\Http\Controllers\CommentController::class);

Route::resource('categories', CategoryController::class);


//Route::group(['middleware' => ['auth']], function () {

Route::resource('books', BookController::class); // was can:edit-books
//  Route::get('books/slug', [BookController::class, 'show'])->middleware('guest');
//  Route::get('/', [BookController::class, 'index'])->name('home');

//Route::resource('podcasts', PodcastController::class);

//Route::get('podcasts', [PodcastController::class, 'index']);
//Route::get('/podcasts/show', [PodcastController::class, 'show']);



// {category} model below
Route::get('/all-cat-books/{category}', [CategoryController::class, 'allCatBooks'])->name('cat-books');

Route::get('/all-podcasts', [PodcastController::class, 'allPodcasts'])->name('all-podcasts');

Route::get('/', [BookController::class, 'welcome'])->name('welcome');

//Route::get('/categories/show', [CategoryController::class, 'show']);

Route::get('test', [TestController::class, 'index']);

//});


Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');
require __DIR__ . '/auth.php';
