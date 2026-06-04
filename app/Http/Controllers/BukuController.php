<?php

namespace App\Http\Controllers;

use App\Models\{Buku, Kategori};
use Illuminate\Http\Request;

class BukuController extends Controller
{
    public function index(Request $request)
    {
        $query = Buku::with('kategori');

        // SEARCH
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('judul_buku', 'like', "%$search%")
                  ->orWhere('kode_buku', 'like', "%$search%")
                  ->orWhere('mata_pelajaran', 'like', "%$search%");
            });
        }

        // FILTER KATEGORI
        if ($request->filled('kategori')) {
            $query->where('kategori_id', $request->kategori);
        }

        // FILTER KELAS
        if ($request->filled('kelas')) {
            $query->where('kelas', $request->kelas);
        }

        $bukus = $query->latest()->paginate(10)->withQueryString();
        $kategoris = Kategori::all();

        return view('buku.index', compact('bukus', 'kategoris'));
    }

    public function create()
    {
        $kategoris = Kategori::all();
        return view('buku.create', compact('kategoris'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode_buku'     => 'required|unique:bukus',
            'judul_buku'    => 'required',
            'pengarang'     => 'required',
            'penerbit'      => 'required',
            'tahun_terbit'  => 'required|digits:4',
            'kategori_id'   => 'required|exists:kategoris,id',
            'kelas'         => 'required',
            'mata_pelajaran'=> 'required',
            'jumlah_buku'   => 'required|integer|min:1',
        ]);

        $data = $request->only([
            'kode_buku',
            'judul_buku',
            'pengarang',
            'penerbit',
            'tahun_terbit',
            'kategori_id',
            'kelas',
            'mata_pelajaran',
            'jumlah_buku'
        ]);

        // STOK AWAL = TOTAL BUKU
        $data['jumlah_tersedia'] = $data['jumlah_buku'];

        Buku::create($data);

        return redirect()->route('buku.index')
            ->with('success', 'Buku berhasil ditambahkan!');
    }

    public function edit(Buku $buku)
    {
        $kategoris = Kategori::all();
        return view('buku.edit', compact('buku', 'kategoris'));
    }

    public function update(Request $request, Buku $buku)
    {
        $request->validate([
            'kode_buku'     => 'required|unique:bukus,kode_buku,' . $buku->id,
            'judul_buku'    => 'required',
            'pengarang'     => 'required',
            'penerbit'      => 'required',
            'tahun_terbit'  => 'required|digits:4',
            'kategori_id'   => 'required|exists:kategoris,id',
            'kelas'         => 'required',
            'mata_pelajaran'=> 'required',
            'jumlah_buku'   => 'required|integer|min:1',
        ]);

        // ambil data aman (tidak termasuk stok)
        $data = $request->only([
            'kode_buku',
            'judul_buku',
            'pengarang',
            'penerbit',
            'tahun_terbit',
            'kategori_id',
            'kelas',
            'mata_pelajaran',
            'jumlah_buku'
        ]);

        // HITUNG SELISIH PERUBAHAN JUMLAH BUKU
        $selisih = $data['jumlah_buku'] - $buku->jumlah_buku;

        // UPDATE STOK SECARA LOGIS
        $data['jumlah_tersedia'] = $buku->jumlah_tersedia + $selisih;

        // proteksi agar tidak minus
        if ($data['jumlah_tersedia'] < 0) {
            $data['jumlah_tersedia'] = 0;
        }

        $buku->update($data);

        return redirect()->route('buku.index')
            ->with('success', 'Buku berhasil diperbarui!');
    }

    public function destroy(Buku $buku)
    {
        $buku->delete();

        return redirect()->route('buku.index')
            ->with('success', 'Buku berhasil dihapus!');
    }

    public function show(Buku $buku)
    {
        return view('buku.show', compact('buku'));
    }
}