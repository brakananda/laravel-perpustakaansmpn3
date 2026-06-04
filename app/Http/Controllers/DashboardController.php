<?php
namespace App\Http\Controllers;
use App\Models\{Buku, Kategori, Anggota, Peminjaman};

class DashboardController extends Controller {
    public function index() {
        $totalBuku       = Buku::sum('jumlah_buku');
        $totalKategori   = Kategori::count();
        $totalAnggota    = Anggota::count();
        $totalPinjam     = Peminjaman::where('status','dipinjam')->count();

        // Breakdown per kategori
        $bukuPerKategori = Kategori::withCount('bukus')
            ->withSum('bukus', 'jumlah_buku')
            ->get();

        return view('dashboard', compact(
            'totalBuku','totalKategori','totalAnggota',
            'totalPinjam','bukuPerKategori'
        ));
    }
}