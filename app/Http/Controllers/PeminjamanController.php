<?php

namespace App\Http\Controllers;

use App\Models\{
    Peminjaman,
    Anggota,
    Buku
};

use Illuminate\Http\Request;
use Carbon\Carbon;

class PeminjamanController extends Controller
{
    /**
     * Improved PeminjamanController dengan:
     * - Validasi status anggota (Aktif only)
     * - Validasi kelas sama
     * - Support peminjaman parsial
     * - Multi-transaction untuk sisa buku
     * - Better error handling
     */

    /**
     * Display a listing of all peminjaman
     * GET /peminjaman
     */
    public function index(Request $request)
    {
        $query = Peminjaman::with([
            'anggota',
            'buku.kategori'
        ]);

        // Search by kode_pinjam or nama_siswa
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('kode_pinjam', 'like', "%{$search}%")
                  ->orWhereHas('anggota', function ($a) use ($search) {
                      $a->where('nama_siswa', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Group by kode_pinjam untuk tampilan yang rapi
        $peminjamans = $query
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('kode_pinjam');

        return view(
            'peminjaman.index',
            compact('peminjamans')
        );
    }

    /**
     * Show form untuk create peminjaman
     * GET /peminjaman/create
     */
    public function create()
    {
        // Hanya ambil anggota yang status = 'Aktif'
        $anggotas = Anggota::where('status', 'Aktif')
                           ->orderBy('nama_siswa')
                           ->get();

        // Ambil semua kategori untuk filter
        $kategoris = \App\Models\Kategori::all();

        // Awalnya kosong, buku di-load via AJAX berdasarkan kelas siswa
        $bukus = collect();

        return view('peminjaman.create', compact(
            'anggotas',
            'bukus',
            'kategoris'
        ));
    }

    /**
     * Store peminjaman baru ke database
     * POST /peminjaman
     * 
     * Logic:
     * 1. Validasi anggota & buku
     * 2. Loop setiap buku yang dipilih:
     *    - Check kelas sama?
     *    - Check stok > 0?
     *    - Check sudah pinjam?
     *    - If semua OK → save
     *    - If ada fail → skip (parsial)
     * 3. Generate kode_pinjam unik
     * 4. Return hasil dengan detail
     */
    public function store(Request $request)
    {
        // Validasi input
        $request->validate([
            'anggota_id'   => 'required|exists:anggotas,id',
            'buku_ids'     => 'required|array|min:1',
            'buku_ids.*'   => 'exists:bukus,id',
        ], [
            'anggota_id.required' => 'Silakan pilih siswa terlebih dahulu',
            'buku_ids.required' => 'Silakan pilih minimal 1 buku',
            'buku_ids.min' => 'Silakan pilih minimal 1 buku',
        ]);

        // Get anggota
        $anggota = Anggota::findOrFail($request->anggota_id);

        // ===== VALIDASI 1: STATUS ANGGOTA =====
        // Blokir alumni & yang keluar dari meminjam
        if (!$anggota->canBorrow()) {
            return back()->withErrors([
                'anggota_id' => "❌ Siswa '{$anggota->nama_siswa}' status '{$anggota->status}' tidak boleh meminjam buku. Hanya siswa 'Aktif' yang diizinkan."
            ])->withInput();
        }

        // Initialize
        $berhasil = 0;
        $gagal = 0;
        $detailGagal = [];
        $tanggalPinjam = now()->toDateString();
        $kodePinjam = 'PJM-' . now()->format('YmdHis');

        // Extract kelas siswa (VII-A → VII)
        $kelasAnggota = $anggota->getClassNumber();

        // ===== LOOP SETIAP BUKU =====
        foreach ($request->buku_ids as $bukuId) {
            $buku = Buku::find($bukuId);

            if (!$buku) {
                $gagal++;
                $detailGagal[] = "Buku ID {$bukuId} tidak ditemukan";
                continue;
            }

            // ===== VALIDASI 2: KELAS HARUS SAMA =====
            $kelasBuku = $buku->getClassNumber ?? $buku->kelas;
            
            if ($kelasAnggota !== $kelasBuku) {
                $gagal++;
                $detailGagal[] = "❌ {$buku->judul_buku}: Kelas buku ({$kelasBuku}) tidak sesuai dengan kelas siswa ({$kelasAnggota})";
                continue;
            }

            // ===== VALIDASI 3: STOK HARUS ADA =====
            if ($buku->jumlah_tersedia <= 0) {
                $gagal++;
                $detailGagal[] = "⚠️ {$buku->judul_buku}: Stok tidak tersedia (0)";
                continue;
            }

            // ===== VALIDASI 4: CEGAH DUPLIKASI =====
            // Siswa tidak boleh pinjam buku yang sama 2x saat status masih 'dipinjam'
            $sudahDipinjam = Peminjaman::where('anggota_id', $anggota->id)
                                       ->where('buku_id', $buku->id)
                                       ->where('status', 'dipinjam')
                                       ->exists();

            if ($sudahDipinjam) {
                $gagal++;
                $detailGagal[] = "⚠️ {$buku->judul_buku}: Sudah dipinjam oleh siswa ini (status: dipinjam)";
                continue;
            }

            // ===== SEMUA VALIDASI PASSED → SAVE PEMINJAMAN =====
            try {
                Peminjaman::create([
                    'kode_pinjam' => $kodePinjam,
                    'anggota_id'  => $anggota->id,
                    'buku_id'     => $buku->id,
                    'tanggal_pinjam' => $tanggalPinjam,
                    'tanggal_kembali' => $tanggalPinjam, // Default sama dengan pinjam
                    'status' => 'dipinjam',
                ]);

                // Kurangi stok buku
                $buku->decrement('jumlah_tersedia');

                $berhasil++;
            } catch (\Exception $e) {
                $gagal++;
                $detailGagal[] = "Error: {$e->getMessage()}";
            }
        }

        // ===== RETURN RESPONSE =====
        if ($berhasil === 0) {
            // Semua gagal
            return back()
                ->withErrors([
                    'buku_ids' => '❌ Tidak ada buku yang valid untuk dipinjam. ' . implode(', ', $detailGagal)
                ])
                ->withInput();
        }

        // Ada yang berhasil (parsial atau full)
        $message = "✅ Peminjaman berhasil dibuat! {$berhasil} buku berhasil dipinjam";
        
        if ($gagal > 0) {
            $message .= " ({$gagal} buku tidak memenuhi kriteria)";
        }

        return redirect()
            ->route('peminjaman.index')
            ->with('success', $message)
            ->with('info', $detailGagal); // Info detail buku yang gagal
    }

    /**
     * Display detail peminjaman
     * GET /peminjaman/{id}
     */
    public function show(Peminjaman $peminjaman)
    {
        return view(
            'peminjaman.show',
            compact('peminjaman')
        );
    }

    /**
     * Edit peminjaman (optional, tidak digunakan sekarang)
     */
    public function edit(Peminjaman $peminjaman)
    {
        // Bisa diisi nanti jika ada kebutuhan
    }

    /**
     * Update peminjaman (optional, tidak digunakan sekarang)
     */
    public function update(Request $request, Peminjaman $peminjaman)
    {
        // Bisa diisi nanti jika ada kebutuhan
    }

    /**
     * Delete/Cancel peminjaman
     * DELETE /peminjaman/{id}
     * 
     * Logic:
     * 1. Find all peminjaman dengan kode_pinjam yang sama
     * 2. Jika status = 'dipinjam', restore stok
     * 3. Delete semua record
     */
    public function destroy(Peminjaman $peminjaman)
    {
        $kodePinjam = $peminjaman->kode_pinjam;

        // Get semua peminjaman dengan kode yang sama
        $semuaPeminjaman = Peminjaman::where('kode_pinjam', $kodePinjam)->get();

        // Loop & restore stok untuk yang belum dikembalikan
        foreach ($semuaPeminjaman as $item) {
            if ($item->status == 'dipinjam') {
                // Restore stok buku
                $item->buku->increment('jumlah_tersedia');
            }

            // Delete record
            $item->delete();
        }

        $jumlah = $semuaPeminjaman->count();

        return redirect()
            ->route('peminjaman.index')
            ->with('success', "✅ Transaksi peminjaman berhasil dibatalkan! ({$jumlah} buku stok di-restore)");
    }

    /**
     * AJAX Endpoint: Get buku per kelas
     * GET /peminjaman/get-buku/{kelas_id}
     * 
     * Digunakan untuk populate dropdown buku berdasarkan kelas siswa
     * Cara: Saat admin pilih siswa → trigger AJAX → load buku per kelas
     */
    public function getBukuByKelas(Request $request)
    {
        $kelas = $request->input('kelas');
        $kategoriId = $request->input('kategori_id');

        $query = Buku::where('kelas', $kelas)
                     ->where('jumlah_tersedia', '>', 0);

        // Filter by kategori jika dipilih
        if ($kategoriId) {
            $query->where('kategori_id', $kategoriId);
        }

        $bukus = $query->orderBy('judul_buku')->get();

        return response()->json($bukus);
    }
}