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
     * Improved PengembalianController v2 dengan:
     * - Checkbox kondisi per buku (OK/RUSAK/HILANG)
     * - Multiple buku dalam 1 transaksi (by kode_pinjam)
     * - Denda rusak/hilang: Rp 70.000/buku
     * - Denda terlambat: Rp 5.000/hari (setelah 7 hari grace period)
     * - Real-time calculation
     * - Fine records auto-created untuk tracking terpisah
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
        // Ambil peminjaman yang status 'dipinjam' + group by kode_pinjam
        $peminjamans = Peminjaman::with(['anggota', 'buku'])
            ->where('status', 'dipinjam')
            ->orderBy('tanggal_pinjam', 'desc')
            ->get();

        // Group by kode_pinjam untuk dropdown
        $groupedByKode = $peminjamans->groupBy('kode_pinjam');

        // Get active return deadline
        $activeDeadline = ReturnDeadline::getActiveDeadline();

        return view('pengembalian.create', compact(
            'groupedByKode',
            'activeDeadline'
        ));
    }

    /**
     * Store pengembalian baru
     * POST /pengembalian
     * 
     * Improved logic:
     * 1. Get SEMUA peminjaman dengan kode_pinjam yang sama
     * 2. Loop setiap buku, check kondisi (OK/RUSAK/HILANG)
     * 3. Calculate denda: rusak/hilang (70k) + terlambat (5k/hari)
     * 4. Create fine records untuk tracking
     * 5. Save 1 pengembalian record (per transaksi)
     */
    public function store(Request $request)
    {
        // Validasi input
        $request->validate([
            'kode_pinjam' => 'required|string',
            'tanggal_kembali_aktual' => 'required|date',
            'buku_kondisi' => 'required|array|min:1',
            'buku_kondisi.*' => 'in:ok,rusak,hilang',
            'buku_keterangan' => 'array',
        ], [
            'kode_pinjam.required' => 'Silakan pilih transaksi peminjaman',
            'tanggal_kembali_aktual.required' => 'Silakan input tanggal pengembalian',
            'buku_kondisi.required' => 'Silakan pilih kondisi untuk setiap buku',
        ]);

        $tanggalKembaliAktual = Carbon::parse($request->tanggal_kembali_aktual);
        $totalDenda = 0;
        $detailDenda = [];
        $bukuDiproses = 0;

        try {
            // ═══════════════════════════════════════════════════════════════
            // STEP 1: GET SEMUA PEMINJAMAN DENGAN KODE YANG SAMA
            // ═══════════════════════════════════════════════════════════════
            $semuaPeminjaman = Peminjaman::with('buku', 'anggota')
                ->where('kode_pinjam', $request->kode_pinjam)
                ->where('status', 'dipinjam')
                ->get();

            if ($semuaPeminjaman->isEmpty()) {
                return back()->withErrors([
                    'error' => '❌ Transaksi peminjaman tidak ditemukan atau sudah dikembalikan'
                ]);
            }

            // ═══════════════════════════════════════════════════════════════
            // STEP 2: LOOP SETIAP BUKU DALAM TRANSAKSI
            // ═══════════════════════════════════════════════════════════════
            foreach ($semuaPeminjaman as $peminjaman) {
                $buku = $peminjaman->buku;
                $bukuId = $buku->id;
                
                // Get kondisi buku dari request
                $kondisi = $request->input("buku_kondisi.{$bukuId}", 'ok');
                $keterangan = $request->input("buku_keterangan.{$bukuId}");

                $bukuDiproses++;

                // ╔═══════════════════════════════════════════════════════╗
                // ║ KONDISI 1: OK → RESTORE STOK + NO DENDA              ║
                // ╚═══════════════════════════════════════════════════════╝
                if ($kondisi === 'ok') {
                    $stokSebelum = $buku->jumlah_tersedia;
                    $buku->increment('jumlah_tersedia');
                    $stokSesudah = $buku->jumlah_tersedia;
                    
                    $detailDenda[] = sprintf(
                        "✅ %s: Kondisi OK - Stok restored (%d → %d)",
                        $buku->judul_buku,
                        $stokSebelum,
                        $stokSesudah
                    );
                    
                    // Update status peminjaman
                    $peminjaman->update(['status' => 'dikembalikan']);
                }

                // ╔═══════════════════════════════════════════════════════╗
                // ║ KONDISI 2: RUSAK → DENDA Rp 70.000 (NO RESTORE)      ║
                // ╚═══════════════════════════════════════════════════════╝
                elseif ($kondisi === 'rusak') {
                    $denda = 70000;
                    $totalDenda += $denda;

                    // Create fine record untuk tracking
                    Fine::create([
                        'anggota_id' => $peminjaman->anggota_id,
                        'buku_id' => $buku->id,
                        'peminjaman_id' => $peminjaman->id,
                        'jenis_denda' => 'rusak',
                        'nominal' => $denda,
                        'status' => 'unpaid',
                        'keterangan' => $keterangan ?? 'Buku rusak saat dikembalikan',
                    ]);

                    $detailDenda[] = sprintf(
                        "⚠️ %s: RUSAK - Denda Rp %s %s",
                        $buku->judul_buku,
                        number_format($denda, 0, ',', '.'),
                        $keterangan ? "({$keterangan})" : ""
                    );

                    // Update status peminjaman (tidak restore stok)
                    $peminjaman->update(['status' => 'dikembalikan']);
                }

                // ╔═══════════════════════════════════════════════════════╗
                // ║ KONDISI 3: HILANG → DENDA Rp 70.000 (NO RESTORE)     ║
                // ╚═══════════════════════════════════════════════════════╝
                elseif ($kondisi === 'hilang') {
                    $denda = 70000;
                    $totalDenda += $denda;

                    // Create fine record untuk tracking
                    Fine::create([
                        'anggota_id' => $peminjaman->anggota_id,
                        'buku_id' => $buku->id,
                        'peminjaman_id' => $peminjaman->id,
                        'jenis_denda' => 'hilang',
                        'nominal' => $denda,
                        'status' => 'unpaid',
                        'keterangan' => $keterangan ?? 'Buku hilang - tidak dikembalikan',
                    ]);

                    $detailDenda[] = sprintf(
                        "❌ %s: HILANG - Denda Rp %s %s",
                        $buku->judul_buku,
                        number_format($denda, 0, ',', '.'),
                        $keterangan ? "({$keterangan})" : ""
                    );

                    // Update status peminjaman (tidak restore stok)
                    $peminjaman->update(['status' => 'dikembalikan']);
                }
            }

            // ═══════════════════════════════════════════════════════════════
            // STEP 3: HITUNG DENDA KETERLAMBATAN (DARI DEADLINE MASSAL)
            // ═══════════════════════════════════════════════════════════════
            $dendaTerlambat = 0;
            $deadline = ReturnDeadline::getActiveDeadline();

            if ($deadline) {
                // Calculate hari terlambat dari deadline
                $hariTerlambat = $deadline->calculateLateDays($tanggalKembaliAktual);

                // ╔═══════════════════════════════════════════════════════╗
                // ║ FORMULA: Jika terlambat > 7 hari, ada denda          ║
                // ║ Denda = (hariTerlambat - 7) * Rp 5.000               ║
                // ╚═══════════════════════════════════════════════════════╝
                if ($hariTerlambat > 7) {
                    $hariTerlambatHitung = $hariTerlambat - 7; // Days beyond grace period
                    $dendaTerlambat = $hariTerlambatHitung * 5000; // Rp 5.000/hari
                    $totalDenda += $dendaTerlambat;

                    // Create 1 fine record untuk denda terlambat
                    // Reference: first peminjaman dalam transaksi
                    $firstPeminjaman = $semuaPeminjaman->first();
                    Fine::create([
                        'anggota_id' => $firstPeminjaman->anggota_id,
                        'buku_id' => $firstPeminjaman->buku_id,
                        'peminjaman_id' => $firstPeminjaman->id,
                        'jenis_denda' => 'terlambat',
                        'nominal' => $dendaTerlambat,
                        'status' => 'unpaid',
                        'keterangan' => "Terlambat {$hariTerlambat} hari (grace period 7 hari, denda mulai hari ke-8) - Rp 5.000/hari | Deadline: " . $deadline->deadline_date->format('d-m-Y'),
                    ]);

                    $detailDenda[] = sprintf(
                        "⏰ KETERLAMBATAN: %d hari (setelah grace 7 hari) - Denda Rp %s",
                        $hariTerlambat,
                        number_format($dendaTerlambat, 0, ',', '.')
                    );
                }
            }

            // ═══════════════════════════════════════════════════════════════
            // STEP 4: SAVE 1 PENGEMBALIAN RECORD (TRANSACTION LEVEL)
            // ═══════════════════════════════════════════════════════════════
            Pengembalian::create([
                'peminjaman_id' => $semuaPeminjaman->first()->id, // Reference first item
                'tanggal_kembali_aktual' => $tanggalKembaliAktual,
                'denda' => $totalDenda,
                'keterangan' => implode(' | ', $detailDenda),
            ]);

            // ═══════════════════════════════════════════════════════════════
            // STEP 5: PREPARE RESPONSE MESSAGE
            // ═══════════════════════════════════════════════════════════════
            $message = "✅ Pengembalian berhasil dicatat!\n\n";
            $message .= "Detail ({$bukuDiproses} buku):\n";
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

        // Get fines terkait dengan peminjaman ini
        $fines = Fine::where('peminjaman_id', $pengembalian->peminjaman_id)
                     ->get();

        // Get semua peminjaman dengan kode yang sama untuk context
        $semuaPeminjaman = Peminjaman::where('kode_pinjam', $pengembalian->peminjaman->kode_pinjam)
                                      ->with('buku')
                                      ->get();

        return view('pengembalian.show', compact('pengembalian', 'fines', 'semuaPeminjaman'));
    }

    /**
     * Delete pengembalian & restore status
     * DELETE /pengembalian/{id}
     */
    public function destroy(Pengembalian $pengembalian)
    {
        // Get semua peminjaman terkait
        $kodePinjam = $pengembalian->peminjaman->kode_pinjam;
        $semuaPeminjaman = Peminjaman::where('kode_pinjam', $kodePinjam)->get();

        try {
            // Delete fine records terkait
            foreach ($semuaPeminjaman as $peminjaman) {
                Fine::where('peminjaman_id', $peminjaman->id)->delete();
                
                // Restore status peminjaman ke 'dipinjam'
                $peminjaman->update(['status' => 'dipinjam']);
                
                // Jika sebelumnya OK (denda=0), kurangi stok kembali
                // Karena stok sudah di-restore saat pengembalian dengan kondisi OK
                if ($pengembalian->denda == 0) {
                    $peminjaman->buku->decrement('jumlah_tersedia');
                }
            }

            // Delete pengembalian
            $pengembalian->delete();

            return redirect()
                ->route('pengembalian.index')
                ->with('success', "✅ Pengembalian berhasil dihapus dan status di-restore!");

        } catch (\Exception $e) {
            return back()->withErrors(['error' => "❌ Error: " . $e->getMessage()]);
        }
    }

    /**
     * AJAX: Get list buku dari transaksi (by kode_pinjam)
     * GET /pengembalian/get-buku-list
     * 
     * Return JSON dengan daftar buku dalam transaksi untuk populate form
     */
    public function getBukuList(Request $request)
    {
        $kodePinjam = $request->input('kode_pinjam');

        $peminjamans = Peminjaman::with('buku', 'anggota')
            ->where('kode_pinjam', $kodePinjam)
            ->where('status', 'dipinjam')
            ->get();

        if ($peminjamans->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi tidak ditemukan'
            ]);
        }

        // Map data buku dengan format untuk form
        $bukuList = $peminjamans->map(function ($peminj) {
            return [
                'peminjaman_id' => $peminj->id,
                'buku_id' => $peminj->buku->id,
                'judul' => $peminj->buku->judul_buku,
                'kode' => $peminj->buku->kode_buku,
                'stok_saat_ini' => $peminj->buku->jumlah_tersedia,
                'penulis' => $peminj->buku->pengarang ?? 'N/A',
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'kode_pinjam' => $kodePinjam,
                'siswa' => $peminjamans->first()->anggota->nama_siswa,
                'kelas' => $peminjamans->first()->anggota->kelas,
                'tanggal_pinjam' => $peminjamans->first()->tanggal_pinjam->format('d-m-Y'),
                'buku' => $bukuList,
            ]
        ]);
    }
}
