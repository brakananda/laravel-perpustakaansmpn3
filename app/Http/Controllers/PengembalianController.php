<?php

namespace App\Http\Controllers;

use App\Models\{
    Pengembalian,
    Peminjaman,
    Fine,
    ReturnDeadline
};

use Illuminate\Http\Request;
use Carbon\Carbon;

class PengembalianController extends Controller
{
    /**
     * Improved PengembalianController dengan:
     * - Tampil list buku dengan checkbox kondisi
     * - Auto calculate denda rusak/hilang (Rp 70.000)
     * - Auto calculate denda terlambat dari deadline massal
     * - Create fine records untuk tracking terpisah
     * - Better error handling & validation
     */

    /**
     * Display list pengembalian
     * GET /pengembalian
     */
    public function index()
    {
        $pengembalians = Pengembalian::with([
            'peminjaman.anggota',
            'peminjaman.buku'
        ])
        ->latest()
        ->paginate(10);

        return view('pengembalian.index', compact('pengembalians'));
    }

    /**
     * Show form create pengembalian
     * GET /pengembalian/create
     */
    public function create()
    {
        // Hanya ambil peminjaman yang status 'dipinjam'
        $peminjamans = Peminjaman::with(['anggota', 'buku'])
            ->where('status', 'dipinjam')
            ->orderBy('tanggal_pinjam', 'desc')
            ->get();

        // Get active return deadline
        $activeDeadline = ReturnDeadline::getActiveDeadline();

        return view('pengembalian.create', compact(
            'peminjamans',
            'activeDeadline'
        ));
    }

    /**
     * Store pengembalian baru
     * POST /pengembalian
     * 
     * Logic:
     * 1. Validasi peminjaman
     * 2. Prepare buku list dengan kondisi (OK/Rusak/Hilang)
     * 3. Calculate denda:
     *    - Per buku rusak/hilang: Rp 70.000
     *    - Keterlambatan: dari deadline massal
     * 4. Create fine records untuk tracking
     * 5. Update status peminjaman
     * 6. Restore stok untuk buku yang OK
     * 7. Save pengembalian record
     */
    public function store(Request $request)
    {
        // Validasi input
        $request->validate([
            'peminjaman_id' => 'required|exists:peminjamans,id',
            'tanggal_kembali_aktual' => 'required|date',
            'buku_kondisi' => 'required|array',
            'buku_kondisi.*' => 'in:ok,rusak,hilang',
            'buku_keterangan' => 'array',
        ], [
            'peminjaman_id.required' => 'Silakan pilih transaksi peminjaman',
            'tanggal_kembali_aktual.required' => 'Silakan input tanggal pengembalian',
            'buku_kondisi.required' => 'Silakan pilih kondisi untuk setiap buku',
        ]);

        // Get peminjaman beserta relasi
        $peminjaman = Peminjaman::with('buku', 'anggota')->findOrFail($request->peminjaman_id);

        $tanggalKembaliAktual = Carbon::parse($request->tanggal_kembali_aktual);
        $totalDenda = 0;
        $detailDenda = [];

        try {
            // ===== LOOP SETIAP BUKU DALAM TRANSAKSI =====
            $kondisiBuku = $request->input('buku_kondisi', []);
            $keteranganBuku = $request->input('buku_keterangan', []);

            // Get kondisi buku dari request
            $bukuKondisiMap = [];
            if (isset($kondisiBuku[$peminjaman->buku_id])) {
                $bukuKondisiMap[$peminjaman->buku_id] = $kondisiBuku[$peminjaman->buku_id];
            }

            $buku = $peminjaman->buku;
            $kondisi = $bukuKondisiMap[$buku->id] ?? 'ok';
            $keterangan = $keteranganBuku[$buku->id] ?? null;

            // ===== KONDISI: OK → RESTORE STOK =====
            if ($kondisi === 'ok') {
                $buku->increment('jumlah_tersedia');
                $detailDenda[] = "✅ {$buku->judul_buku}: Kondisi OK - Stok di-restore";
            }

            // ===== KONDISI: RUSAK → CREATE FINE RECORD =====
            elseif ($kondisi === 'rusak') {
                $denda = 70000; // Rp 70.000 per buku rusak
                $totalDenda += $denda;

                Fine::create([
                    'anggota_id' => $peminjaman->anggota_id,
                    'buku_id' => $buku->id,
                    'peminjaman_id' => $peminjaman->id,
                    'jenis_denda' => 'rusak',
                    'nominal' => $denda,
                    'status' => 'unpaid',
                    'keterangan' => $keterangan ?? 'Buku rusak saat dikembalikan',
                ]);

                $detailDenda[] = "⚠️ {$buku->judul_buku}: RUSAK - Denda: Rp " . number_format($denda, 0, ',', '.');
            }

            // ===== KONDISI: HILANG → CREATE FINE RECORD =====
            elseif ($kondisi === 'hilang') {
                $denda = 70000; // Rp 70.000 per buku hilang
                $totalDenda += $denda;

                Fine::create([
                    'anggota_id' => $peminjaman->anggota_id,
                    'buku_id' => $buku->id,
                    'peminjaman_id' => $peminjaman->id,
                    'jenis_denda' => 'hilang',
                    'nominal' => $denda,
                    'status' => 'unpaid',
                    'keterangan' => $keterangan ?? 'Buku hilang - tidak dikembalikan',
                ]);

                $detailDenda[] = "❌ {$buku->judul_buku}: HILANG - Denda: Rp " . number_format($denda, 0, ',', '.');
            }

            // ===== HITUNG DENDA KETERLAMBATAN =====
            $dendaTerlambat = 0;
            $statusPeminjaman = 'dikembalikan'; // Default: tidak terlambat

            // Get active return deadline
            $deadline = ReturnDeadline::getActiveDeadline();

            if ($deadline) {
                // Cek apakah terlambat dari deadline massal
                $hariTerlambat = $tanggalKembaliAktual->diffInDays($deadline->deadline_date);

                // Jika terlambat lebih dari 7 hari, ada denda terlambat
                if ($tanggalKembaliAktual->greaterThan($deadline->deadline_date) && $hariTerlambat > 7) {
                    // Denda terlambat: Rp 5.000 per hari (setelah hari ke-7)
                    $hariTerlambatHitung = $hariTerlambat - 7;
                    $dendaTerlambat = $hariTerlambatHitung * 5000;
                    $totalDenda += $dendaTerlambat;
                    $statusPeminjaman = 'terlambat';

                    // Create fine record untuk denda terlambat
                    Fine::create([
                        'anggota_id' => $peminjaman->anggota_id,
                        'buku_id' => $buku->id,
                        'peminjaman_id' => $peminjaman->id,
                        'jenis_denda' => 'terlambat',
                        'nominal' => $dendaTerlambat,
                        'status' => 'unpaid',
                        'keterangan' => "Terlambat {$hariTerlambat} hari (setelah deadline: {$deadline->deadline_date->format('d-m-Y')})",
                    ]);

                    $detailDenda[] = "⏰ KETERLAMBATAN: {$hariTerlambat} hari - Denda: Rp " . number_format($dendaTerlambat, 0, ',', '.');
                }
            }

            // ===== SAVE PENGEMBALIAN =====
            Pengembalian::create([
                'peminjaman_id' => $peminjaman->id,
                'tanggal_kembali_aktual' => $tanggalKembaliAktual,
                'denda' => $totalDenda,
                'keterangan' => implode(' | ', $detailDenda),
            ]);

            // ===== UPDATE STATUS PEMINJAMAN =====
            $peminjaman->update([
                'status' => $statusPeminjaman,
            ]);

            // ===== PREPARE RESPONSE MESSAGE =====
            $message = "✅ Pengembalian berhasil dicatat!\n\n";
            $message .= "Detail:\n";
            $message .= implode("\n", $detailDenda) . "\n\n";
            $message .= "💰 TOTAL DENDA: Rp " . number_format($totalDenda, 0, ',', '.');

            return redirect()
                ->route('pengembalian.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            return back()
                ->withErrors(['error' => "❌ Error: " . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Show detail pengembalian
     * GET /pengembalian/{id}
     */
    public function show(Pengembalian $pengembalian)
    {
        $pengembalian->load('peminjaman.anggota', 'peminjaman.buku');

        // Get fines untuk pengembalian ini
        $fines = Fine::where('peminjaman_id', $pengembalian->peminjaman_id)
                     ->get();

        return view('pengembalian.show', compact('pengembalian', 'fines'));
    }

    /**
     * Edit pengembalian (optional)
     */
    public function edit(Pengembalian $pengembalian)
    {
        // Bisa diisi nanti jika perlu edit
    }

    /**
     * Update pengembalian (optional)
     */
    public function update(Request $request, Pengembalian $pengembalian)
    {
        // Bisa diisi nanti jika perlu update
    }

    /**
     * Delete pengembalian
     * DELETE /pengembalian/{id}
     */
    public function destroy(Pengembalian $pengembalian)
    {
        $peminjaman = $pengembalian->peminjaman;
        $bukuTitle = $peminjaman->buku->judul_buku;

        // Delete fine records terkait
        Fine::where('peminjaman_id', $peminjaman->id)->delete();

        // Restore status peminjaman ke 'dipinjam'
        $peminjaman->update(['status' => 'dipinjam']);

        // Jika sebelumnya OK dan stok di-restore, kurangi lagi
        if ($pengembalian->denda == 0) {
            $peminjaman->buku->decrement('jumlah_tersedia');
        }

        // Delete pengembalian
        $pengembalian->delete();

        return redirect()
            ->route('pengembalian.index')
            ->with('success', "✅ Pengembalian '{$bukuTitle}' berhasil dihapus!");
    }

    /**
     * AJAX: Get list buku dari peminjaman tertentu
     * GET /pengembalian/get-buku-list/{peminjaman_id}
     * 
     * Digunakan untuk populate checkbox buku saat pilih peminjaman
     */
    public function getBukuList($peminjamanId)
    {
        $peminjaman = Peminjaman::with('buku')
            ->findOrFail($peminjamanId);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $peminjaman->id,
                'kode_pinjam' => $peminjaman->kode_pinjam,
                'siswa' => $peminjaman->anggota->nama_siswa,
                'buku' => [
                    'id' => $peminjaman->buku->id,
                    'judul' => $peminjaman->buku->judul_buku,
                    'kode' => $peminjaman->buku->kode_buku,
                ],
                'tanggal_pinjam' => $peminjaman->tanggal_pinjam->format('d-m-Y'),
                'tanggal_kembali_batas' => $peminjaman->tanggal_kembali->format('d-m-Y'),
            ]
        ]);
    }
}