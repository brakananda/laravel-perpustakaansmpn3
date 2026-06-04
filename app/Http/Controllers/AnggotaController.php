<?php

namespace App\Http\Controllers;

use App\Models\Anggota;
use Illuminate\Http\Request;

class AnggotaController extends Controller
{
    /**
     * PHASE 5: Improved AnggotaController dengan:
     * - Mass update status per angkatan
     * - Import Excel untuk data siswa baru
     * - Filter & search functionality
     */

    /**
     * Display listing anggota dengan filter
     * GET /anggota
     */
    public function index(Request $request)
    {
        $query = Anggota::query();

        // Search by nama atau NIS
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_siswa', 'like', "%{$search}%")
                  ->orWhere('nis', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by angkatan
        if ($request->filled('angkatan')) {
            $query->where('angkatan', $request->angkatan);
        }

        // Filter by kelas
        if ($request->filled('kelas')) {
            $query->where('kelas', $request->kelas);
        }

        $anggotas = $query->orderBy('nama_siswa')->paginate(15);

        // Get filter options
        $angkatanList = Anggota::distinct()->pluck('angkatan')->sort();
        $kelasList = Anggota::distinct()->pluck('kelas')->sort();
        $statusList = ['Aktif', 'Alumni', 'Keluar'];

        return view('anggota.index', compact('anggotas', 'angkatanList', 'kelasList', 'statusList'));
    }

    /**
     * Show form create anggota
     * GET /anggota/create
     */
    public function create()
    {
        $statusList = ['Aktif', 'Alumni', 'Keluar'];
        $kelasList = ['VII-A', 'VII-B', 'VII-C', 'VIII-A', 'VIII-B', 'VIII-C', 'IX-A', 'IX-B', 'IX-C'];
        $angkatanList = range(2020, date('Y'));

        return view('anggota.create', compact('statusList', 'kelasList', 'angkatanList'));
    }

    /**
     * Store anggota baru
     * POST /anggota
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nis' => 'required|unique:anggotas,nis',
            'nama_siswa' => 'required|string|max:100',
            'kelas' => 'required|string',
            'jenis_kelamin' => 'required|in:L,P',
            'alamat' => 'nullable|string|max:255',
            'no_telp' => 'nullable|string|max:20',
            'status' => 'required|in:Aktif,Alumni,Keluar',
            'angkatan' => 'required|integer|min:2000|max:' . date('Y'),
        ], [
            'nis.unique' => 'NIS sudah terdaftar di sistem',
        ]);

        Anggota::create($validated);

        return redirect()->route('anggota.index')
                       ->with('success', "✅ Anggota '{$validated['nama_siswa']}' berhasil ditambahkan!");
    }

    /**
     * Show detail anggota
     * GET /anggota/{anggota}
     */
    public function show(Anggota $anggota)
    {
        $anggota->load('peminjamans.buku', 'fines');
        
        return view('anggota.show', compact('anggota'));
    }

    /**
     * Show form edit anggota
     * GET /anggota/{anggota}/edit
     */
    public function edit(Anggota $anggota)
    {
        $statusList = ['Aktif', 'Alumni', 'Keluar'];
        $kelasList = ['VII-A', 'VII-B', 'VII-C', 'VIII-A', 'VIII-B', 'VIII-C', 'IX-A', 'IX-B', 'IX-C'];
        $angkatanList = range(2020, date('Y'));

        return view('anggota.edit', compact('anggota', 'statusList', 'kelasList', 'angkatanList'));
    }

    /**
     * Update anggota
     * PUT /anggota/{anggota}
     */
    public function update(Request $request, Anggota $anggota)
    {
        $validated = $request->validate([
            'nis' => "required|unique:anggotas,nis,{$anggota->id}",
            'nama_siswa' => 'required|string|max:100',
            'kelas' => 'required|string',
            'jenis_kelamin' => 'required|in:L,P',
            'alamat' => 'nullable|string|max:255',
            'no_telp' => 'nullable|string|max:20',
            'status' => 'required|in:Aktif,Alumni,Keluar',
            'angkatan' => 'required|integer|min:2000|max:' . date('Y'),
        ]);

        $anggota->update($validated);

        return redirect()->route('anggota.show', $anggota)
                       ->with('success', "✅ Data anggota berhasil diperbarui!");
    }

    /**
     * Delete anggota
     * DELETE /anggota/{anggota}
     */
    public function destroy(Anggota $anggota)
    {
        $nama = $anggota->nama_siswa;
        $anggota->delete();

        return redirect()->route('anggota.index')
                       ->with('success', "✅ Anggota '{$nama}' berhasil dihapus!");
    }

    /**
     * ═══════════════════════════════════════════════════════════
     * PHASE 5: MASS UPDATE STATUS PER ANGKATAN
     * ═══════════════════════════════════════════════════════════
     * GET /anggota/mass-update
     */
    public function showMassUpdate()
    {
        $angkatanList = Anggota::distinct()->orderBy('angkatan', 'desc')->pluck('angkatan');
        $statusList = ['Aktif', 'Alumni', 'Keluar'];

        // Get summary per angkatan
        $summary = Anggota::selectRaw('angkatan, status, COUNT(*) as total')
                          ->groupBy('angkatan', 'status')
                          ->orderBy('angkatan', 'desc')
                          ->get();

        return view('anggota.mass-update', compact('angkatanList', 'statusList', 'summary'));
    }

    /**
     * Process mass update status
     * POST /anggota/mass-update
     */
    public function processMassUpdate(Request $request)
    {
        $validated = $request->validate([
            'angkatan' => 'required|integer',
            'status_baru' => 'required|in:Aktif,Alumni,Keluar',
        ], [
            'angkatan.required' => 'Silakan pilih angkatan',
            'status_baru.required' => 'Silakan pilih status baru',
        ]);

        // Update status untuk semua anggota dengan angkatan yang dipilih
        $jumlahDiupdate = Anggota::where('angkatan', $validated['angkatan'])
                                  ->update(['status' => $validated['status_baru']]);

        if ($jumlahDiupdate > 0) {
            return redirect()->route('anggota.mass-update')
                           ->with('success', "✅ Berhasil update status $jumlahDiupdate anggota angkatan {$validated['angkatan']} menjadi '{$validated['status_baru']}'!");
        } else {
            return back()->withErrors(['error' => '❌ Tidak ada anggota dengan angkatan tersebut']);
        }
    }

    /**
     * ═══════════════════════════════════════════════════════════
     * PHASE 5: IMPORT EXCEL SISWA BARU
     * ═══════════════════════════════════════════════════════════
     * GET /anggota/import
     */
    public function showImport()
    {
        return view('anggota.import');
    }

    /**
     * Process import Excel
     * POST /anggota/import
     * 
     * Required columns: NIS, Nama Siswa, Kelas, Jenis Kelamin, Angkatan
     * Optional columns: Alamat, No Telp
     */
    public function processImport(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:5120', // 5MB max
        ], [
            'file.required' => 'Silakan upload file Excel',
            'file.mimes' => 'File harus berformat Excel (.xlsx, .xls, .csv)',
            'file.max' => 'Ukuran file maksimal 5MB',
        ]);

        try {
            // Import menggunakan library Laravel Excel
            $file = $request->file('file');
            $path = $file->store('imports', 'local');

            // Read Excel using PhpSpreadsheet
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load(storage_path("app/{$path}"));
            $worksheet = $spreadsheet->getActiveSheet();

            $rows = $worksheet->toArray();
            $header = array_shift($rows); // Get header row

            $imported = 0;
            $skipped = 0;
            $errors = [];

            // Loop setiap row
            foreach ($rows as $index => $row) {
                $rowNumber = $index + 2; // +1 for header, +1 for 0-based index

                // Skip empty rows
                if (empty($row[0]) && empty($row[1])) {
                    continue;
                }

                try {
                    // Map columns
                    $nis = trim($row[0] ?? '');
                    $nama = trim($row[1] ?? '');
                    $kelas = trim($row[2] ?? '');
                    $jenisKelamin = trim($row[3] ?? '');
                    $angkatan = (int)($row[4] ?? date('Y'));
                    $alamat = trim($row[5] ?? '');
                    $noTelp = trim($row[6] ?? '');

                    // Validate required fields
                    if (empty($nis) || empty($nama) || empty($kelas) || empty($jenisKelamin)) {
                        $errors[] = "Baris {$rowNumber}: Data tidak lengkap (NIS, Nama, Kelas, Jenis Kelamin wajib diisi)";
                        $skipped++;
                        continue;
                    }

                    // Validate jenis kelamin
                    if (!in_array(strtoupper($jenisKelamin), ['L', 'P'])) {
                        $errors[] = "Baris {$rowNumber}: Jenis Kelamin harus 'L' atau 'P'";
                        $skipped++;
                        continue;
                    }

                    // Check if NIS already exists
                    if (Anggota::where('nis', $nis)->exists()) {
                        $errors[] = "Baris {$rowNumber}: NIS '{$nis}' sudah terdaftar (skip)";
                        $skipped++;
                        continue;
                    }

                    // Create new anggota
                    Anggota::create([
                        'nis' => $nis,
                        'nama_siswa' => $nama,
                        'kelas' => $kelas,
                        'jenis_kelamin' => strtoupper($jenisKelamin),
                        'alamat' => $alamat ?: null,
                        'no_telp' => $noTelp ?: null,
                        'status' => 'Aktif',
                        'angkatan' => $angkatan,
                    ]);

                    $imported++;

                } catch (\Exception $e) {
                    $errors[] = "Baris {$rowNumber}: {$e->getMessage()}";
                    $skipped++;
                }
            }

            // Delete uploaded file
            \Storage::disk('local')->delete($path);

            // Prepare response message
            $message = "✅ Import Excel berhasil!\n\n";
            $message .= "📊 Hasil:\n";
            $message .= "• Berhasil diimpor: $imported anggota\n";
            $message .= "• Dilewatkan: $skipped baris\n";

            if (!empty($errors)) {
                $message .= "\n❌ Error/Warning:\n";
                $message .= implode("\n", array_slice($errors, 0, 10)); // Show first 10 errors
                if (count($errors) > 10) {
                    $message .= "\n... dan " . (count($errors) - 10) . " error lainnya";
                }
            }

            return redirect()->route('anggota.index')
                           ->with('success', $message);

        } catch (\Exception $e) {
            return back()->withErrors(['error' => "❌ Error import: " . $e->getMessage()]);
        }
    }

    /**
     * Download template Excel untuk import
     * GET /anggota/download-template
     */
    public function downloadTemplate()
    {
        // Create new Spreadsheet
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set header
        $headers = ['NIS', 'Nama Siswa', 'Kelas', 'Jenis Kelamin (L/P)', 'Angkatan', 'Alamat', 'No Telp'];
        $sheet->fromArray([$headers], null, 'A1');

        // Style header
        $headerStyle = [
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4CAF50'],
            ],
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'alignment' => [
                'horizontal' => 'center',
                'vertical' => 'center',
            ],
        ];
        $sheet->getStyle('A1:G1')->applyFromArray($headerStyle);

        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(15);
        $sheet->getColumnDimension('B')->setWidth(30);
        $sheet->getColumnDimension('C')->setWidth(12);
        $sheet->getColumnDimension('D')->setWidth(18);
        $sheet->getColumnDimension('E')->setWidth(12);
        $sheet->getColumnDimension('F')->setWidth(30);
        $sheet->getColumnDimension('G')->setWidth(15);

        // Add sample data
        $samples = [
            ['001', 'Billal Rakananda', 'VII-A', 'L', date('Y'), 'Jl. Merdeka No. 1', '08123456789'],
            ['002', 'Siti Nurhaliza', 'VII-A', 'P', date('Y'), 'Jl. Ahmad Yani No. 2', '08987654321'],
            ['003', 'Bambang Irawan', 'VII-B', 'L', date('Y'), '', ''],
        ];
        $sheet->fromArray($samples, null, 'A2');

        // Create writer
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        // Set headers untuk download
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Template_Import_Anggota.xlsx"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }
}
