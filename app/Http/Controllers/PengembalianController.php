<?php
namespace App\Http\Controllers;
use App\Models\{Pengembalian, Peminjaman};
use Illuminate\Http\Request;
use Carbon\Carbon;

class PengembalianController extends Controller {
    public function index() {
        $pengembalians = Pengembalian::with(['peminjaman.anggota','peminjaman.buku'])
            ->latest()->paginate(10);
        return view('pengembalian.index', compact('pengembalians'));
    }
    public function create() {
        $peminjamans = Peminjaman::with(['anggota','buku'])
            ->where('status','dipinjam')->get();
        return view('pengembalian.create', compact('peminjamans'));
    }
    public function store(Request $request) {
        $request->validate([
            'peminjaman_id'         => 'required|exists:peminjamans,id',
            'tanggal_kembali_aktual'=> 'required|date',
        ]);
        $peminjaman = Peminjaman::findOrFail($request->peminjaman_id);
        $tglBatas   = Carbon::parse($peminjaman->tanggal_kembali);
        $tglAktual  = Carbon::parse($request->tanggal_kembali_aktual);
        $denda = 0;
        if ($tglAktual->gt($tglBatas)) {
            $mingguTerlambat = ceil($tglAktual->diffInDays($tglBatas) / 7);
            $denda = $mingguTerlambat * 15000;
        }
        Pengembalian::create([
            'peminjaman_id'          => $request->peminjaman_id,
            'tanggal_kembali_aktual' => $request->tanggal_kembali_aktual,
            'denda'                  => $denda,
            'keterangan'             => $request->keterangan,
        ]);
        $status = $denda > 0 ? 'terlambat' : 'dikembalikan';
        $peminjaman->update(['status' => $status]);
        // Tambah stok kembali
        $peminjaman->buku->increment('jumlah_tersedia');
        return redirect()->route('pengembalian.index')
            ->with('success','Pengembalian dicatat! Denda: Rp '.number_format($denda,0,',','.'));
    }
    public function show(Pengembalian $pengembalian) {}
    public function edit(Pengembalian $pengembalian) {}
    public function update(Request $request, Pengembalian $pengembalian) {}
    public function destroy(Pengembalian $pengembalian) {
        $pengembalian->delete();
        return redirect()->route('pengembalian.index')->with('success','Data dihapus!');
    }
}