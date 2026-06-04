<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\BukuController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\AnggotaController;
use App\Http\Controllers\PeminjamanController;
use App\Http\Controllers\PengembalianController;

// ── Halaman Utama → redirect ke login
Route::get('/', fn() => redirect()->route('login'));

// ── Auth
Route::get('/login',  [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::get('/forgot-password', function () { return view('auth.forgot-password'); })->name('forgot-password');
Route::post('/logout',[AuthController::class, 'logout'])->name('logout');

// ── Halaman yang butuh login
Route::middleware('auth')->group(function () {
    Route::get('/home', [DashboardController::class, 'index'])->name('home');

    Route::resource('buku',        BukuController::class);
    Route::resource('kategori',    KategoriController::class);

    Route::resource('anggota', AnggotaController::class)
    ->parameters([
        'anggota' => 'anggota'
    ]);
    
    Route::resource('peminjaman',  PeminjamanController::class);
    Route::resource('pengembalian',PengembalianController::class);
});