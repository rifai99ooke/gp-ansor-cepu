<?php

declare(strict_types=1);

use App\Http\Controllers\BodyController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OfficerController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\RantingController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/berita', [PostController::class, 'beritaIndex'])->name('berita.index');
Route::get('/berita/{slug}', [PostController::class, 'beritaShow'])->name('berita.show');

Route::prefix('profil')->name('profil.')->group(function (): void {
    Route::get('/visi-misi', [PageController::class, 'visiMisi'])->name('visi-misi');
    Route::get('/struktur-organisasi', [OfficerController::class, 'index'])->name('struktur');
    Route::get('/struktur-organisasi/{slug}', [OfficerController::class, 'show'])->name('struktur.show');
});

Route::get('/badan-lembaga', [BodyController::class, 'index'])->name('badan-lembaga.index');
Route::get('/badan-lembaga/{slug}', [BodyController::class, 'show'])->name('badan-lembaga.show');

Route::get('/ranting', [RantingController::class, 'index'])->name('ranting.index');
Route::get('/ranting/{slug}', [RantingController::class, 'show'])->name('ranting.show');

Route::get('/galeri', [GalleryController::class, 'index'])->name('galeri.index');
Route::get('/hubungi-kami', [ContactController::class, 'index'])->name('hubungi-kami');

Route::get('/pendaftaran', [RegistrationController::class, 'index'])->name('pendaftaran.index');
Route::post('/pendaftaran', [RegistrationController::class, 'submit'])->middleware('throttle:10,1')->name('pendaftaran.submit');

Route::get('/program', [PageController::class, 'program'])->name('page.program');
Route::get('/dokumen', [PageController::class, 'dokumen'])->name('page.dokumen');

Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
