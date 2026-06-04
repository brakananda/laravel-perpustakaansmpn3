<?php

namespace App\Http\Controllers;

use App\Models\{
    Peminjaman,
    Anggota,
    Buku
};

use Illuminate\Http\Request;

class PeminjamanController extends Controller
{
    public function index(Request $request)
    {
        $query = Peminjaman::with([
            'anggota',
            'buku.kategori'
        ]);

        // Search
        if ($request->filled('search')) {

            $search = $request->search;

            $query->where(function ($q) use ($search) {

                $q->where('kode_pinjam', 'like', "%{$search}%")

                ->orWhereHas('anggota', function ($a) use ($search) {

                    $a->where('nama_siswa', 'like', "%{$search}%");

                });

            });
        }

        // Filter Status
        if ($request->filled('status')) {

            $query->where('status', $request->status);

        }

        $peminjamans = $query
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('kode_pinjam');

        return view(
            'peminjaman.index',
            compact('peminjamans')
        );
    }

    public function create()
    {
        $anggotas = Anggota::orderBy('nama_siswa')->get();
        $bukus = Buku::with('kategori')
            ->where('jumlah_tersedia','>',0)
            ->orderBy('judul_buku')
            ->get();

        $kategoris = \App\Models\Kategori::all();

        return view('peminjaman.create', compact(
            'anggotas',
            'bukus',
            'kategoris'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'anggota_id'   => 'required|exists:anggotas,id',
            'buku_ids'     => 'required|array|min:1',
            'buku_ids.*'   => 'exists:bukus,id',
        ]);

        $anggota = Anggota::findOrFail($request->anggota_id);
        $berhasil = 0;
        $tanggalPinjam = now()->toDateString();
        $kodePinjam = 'PJM-' . now()->format('YmdHis');

        foreach ($request->buku_ids as $bukuId) {

            $buku = Buku::find($bukuId);

            if (!$buku) continue;

            $sudahDipinjam = Peminjaman::where(
                'anggota_id',
                $anggota->id
            )
            ->where(
                'buku_id',
                $buku->id
            )
            ->where(
                'status',
                'dipinjam'
            )
            ->exists();

        if ($sudahDipinjam) {
            continue;
        }

            // RULE: kelas harus sama
            $kelasAnggota =
            explode('-', $anggota->kelas)[0];

            $kelasBuku =
            explode('-', $buku->kelas)[0];

            if ($kelasAnggota !== $kelasBuku) {
                continue;
            }

            // stok check
            if ($buku->jumlah_tersedia <= 0) continue;

            Peminjaman::create([
                'kode_pinjam' => $kodePinjam,
                'anggota_id'  => $anggota->id,
                'buku_id'     => $buku->id,
                'tanggal_pinjam' => $tanggalPinjam,
                'tanggal_kembali' => $tanggalPinjam,
                'status' => 'dipinjam',
            ]);

            $buku->decrement('jumlah_tersedia');

            $berhasil++;
        }

        if ($berhasil === 0) {
            return back()->withErrors([
                'buku_ids' => 'Tidak ada buku yang valid (kelas atau stok).'
            ]);
        }

        return redirect()
            ->route('peminjaman.index')
            ->with('success', "$berhasil buku berhasil dipinjam");
    }
    public function show(Peminjaman $peminjaman)
    {
        return view(
            'peminjaman.show',
            compact('peminjaman')
        );
    }

    public function edit(Peminjaman $peminjaman)
    {
        //
    }

    public function update(
        Request $request,
        Peminjaman $peminjaman
    )
    {
        //
    }

    public function destroy(Peminjaman $peminjaman)
    {
        $semuaPeminjaman = Peminjaman::where(
            'kode_pinjam',
            $peminjaman->kode_pinjam
        )->get();

        foreach ($semuaPeminjaman as $item)
        {
            if ($item->status == 'dipinjam')
            {
                $item->buku->increment(
                    'jumlah_tersedia'
                );
            }

            $item->delete();
        }

        return redirect()
            ->route('peminjaman.index')
            ->with(
                'success',
                'Transaksi peminjaman berhasil dihapus.'
            );
    }
}