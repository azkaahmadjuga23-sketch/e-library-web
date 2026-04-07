<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

// ─── Auth ─────────────────────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login',    [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login',   [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register',[AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// ─── Public ───────────────────────────────────────────────────────────────────
Route::get('/', [HomeController::class, 'index'])->name('books.index');
Route::get('/search', [HomeController::class, 'search'])->name('books.search');
Route::get('/books/{olKey}', [BookController::class, 'show'])->name('books.show');

// ─── Protected (Auth Required) ────────────────────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::get('/library',                        [BookController::class, 'library'])->name('books.library');
    Route::get('/books/{book}/read',              [BookController::class, 'read'])->name('books.read');
    Route::post('/books/{book}/wishlist',         [BookController::class, 'toggleWishlist'])->name('books.wishlist');
    Route::post('/books/{book}/finish',           [BookController::class, 'markFinished'])->name('books.finish');
});
