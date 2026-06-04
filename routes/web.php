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

// ── Auth Routes
Route::get('/login',  [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::get('/forgot-password', function () { return view('auth.forgot-password'); })->name('forgot-password');
Route::post('/logout',[AuthController::class, 'logout'])->name('logout');

// ── Halaman yang butuh login (Protected)
Route::middleware('auth')->group(function () {
    
    // Dashboard
    Route::get('/home', [DashboardController::class, 'index'])->name('home');

    // ──────────────────────────────────────────────────────────────
    // MASTER DATA ROUTES
    // ──────────────────────────────────────────────────────────────
    Route::resource('buku',        BukuController::class);
    Route::resource('kategori',    KategoriController::class);

    // ──────────────────────────────────────────────────────────────
    // ANGGOTA ROUTES (+ PHASE 5: Mass Update & Import)
    // ──────────────────────────────────────────────────────────────
    Route::resource('anggota', AnggotaController::class)
        ->parameters([
            'anggota' => 'anggota'
        ]);

    // PHASE 5: Mass Update Status per Angkatan
    Route::get('/anggota-mass-update', [AnggotaController::class, 'showMassUpdate'])->name('anggota.mass-update');
    Route::post('/anggota-mass-update', [AnggotaController::class, 'processMassUpdate'])->name('anggota.mass-update.process');

    // PHASE 5: Import Excel
    Route::get('/anggota-import', [AnggotaController::class, 'showImport'])->name('anggota.import');
    Route::post('/anggota-import', [AnggotaController::class, 'processImport'])->name('anggota.import.process');
    Route::get('/anggota-download-template', [AnggotaController::class, 'downloadTemplate'])->name('anggota.download-template');

    // ──────────────────────────────────────────────────────────────
    // TRANSAKSI ROUTES (Peminjaman & Pengembalian)
    // ──────────────────────────────────────────────────────────────
    Route::resource('peminjaman',  PeminjamanController::class);
    
    // AJAX: Get buku per kelas
    Route::get('/peminjaman/get-buku', [PeminjamanController::class, 'getBukuByKelas'])->name('peminjaman.get-buku');
    
    Route::resource('pengembalian', PengembalianController::class);
    
    // AJAX: Get buku list untuk pengembalian (by kode_pinjam)
    Route::get('/pengembalian/get-buku-list', [PengembalianController::class, 'getBukuList'])->name('pengembalian.get-buku-list');
});
