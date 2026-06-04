@extends('layouts.app')

@section('title','Catat Pengembalian')

@section('content')

<div class="container-fluid px-4 py-3">
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-success text-white fw-bold">
            <i class="bi bi-arrow-bar-down me-2"></i>Catat Pengembalian Buku Kurikulum
        </div>

        <div class="card-body">
            {{-- Error Messages --}}
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>❌ Error:</strong>
                    <ul class="mb-0 mt-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <form action="{{ route('pengembalian.store') }}" method="POST" id="formPengembalian">
                @csrf

                {{-- ════════════════════════════════════════════════════ --}}
                {{-- SECTION 1: PILIH TRANSAKSI PEMINJAMAN               --}}
                {{-- ════════════════════════════════════════════════════ --}}
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">
                            <i class="bi bi-journal-bookmark me-2"></i>Pilih Transaksi Peminjaman (Kode)
                        </label>
                        <select name="kode_pinjam" id="kodePinjam" class="form-select form-select-lg" required onchange="loadBukuList()">
                            <option value="">-- Pilih Transaksi --</option>
                            @foreach($groupedByKode as $kode => $items)
                                @php
                                    $siswa = $items->first()->anggota->nama_siswa;
                                    $jumlahBuku = $items->count();
                                @endphp
                                <option value="{{ $kode }}">
                                    [{{ $kode }}] {{ $siswa }} ({{ $jumlahBuku }} buku)
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">
                            <i class="bi bi-calendar-event me-2"></i>Tanggal Pengembalian Aktual
                        </label>
                        <input type="date" name="tanggal_kembali_aktual" class="form-control form-control-lg" 
                               value="{{ old('tanggal_kembali_aktual', now()->toDateString()) }}" required>
                    </div>
                </div>

                {{-- ════════════════════════════════════════════════════ --}}
                {{-- SECTION 2: DEADLINE MASSAL INFO                     --}}
                {{-- ════════════════════════════════════════════════════ --}}
                @if($activeDeadline)
                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                        <div class="row align-items-center g-3">
                            <div class="col-auto">
                                <i class="bi bi-calendar2-check display-6 text-info"></i>
                            </div>
                            <div class="col">
                                <h6 class="mb-2 fw-bold">📅 Deadline Massal Pengembalian Buku</h6>
                                <p class="mb-2">
                                    <strong>Tanggal Batas:</strong> {{ $activeDeadline->deadline_date->format('d F Y') }}
                                </p>
                                @if($activeDeadline->note)
                                    <p class="mb-2">
                                        <strong>Catatan:</strong> {{ $activeDeadline->note }}
                                    </p>
                                @endif
                                <p class="mb-0 small text-muted">
                                    <strong>Formula Denda Keterlambatan:</strong>
                                    <br>✅ Tepat waktu (≤ deadline): Tidak ada denda
                                    <br>⏰ Terlambat 1-7 hari: Grace period (tidak ada denda)
                                    <br>⏰ Terlambat > 7 hari: Rp 5.000 per hari (mulai hari ke-8)
                                </p>
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @else
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>⚠️ Tidak ada deadline massal aktif.</strong>
                        Denda keterlambatan tidak akan dihitung. 
                        <a href="#" class="alert-link">Setup deadline di menu Settings</a>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                {{-- ════════════════════════════════════════════════════ --}}
                {{-- SECTION 3: DAFTAR BUKU DENGAN CHECKBOX KONDISI      --}}
                {{-- ════════════════════════════════════════════════════ --}}
                <div id="bukuContainer" class="mb-4">
                    <div class="text-center text-muted py-5">
                        <i class="bi bi-inbox display-3 text-muted mb-3"></i>
                        <p>Pilih transaksi untuk menampilkan daftar buku...</p>
                    </div>
                </div>

                {{-- ════════════════════════════════════════════════════ --}}
                {{-- SECTION 4: TOMBOL SUBMIT                            --}}
                {{-- ════════════════════════════════════════════════════ --}}
                <div class="d-flex gap-2 justify-content-between">
                    <div>
                        <a href="{{ route('pengembalian.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i>Batal
                        </a>
                    </div>
                    <button type="submit" class="btn btn-success btn-lg" id="btnSubmit" disabled>
                        <i class="bi bi-save me-1"></i>Simpan Pengembalian
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
/**
 * AJAX: Load daftar buku berdasarkan kode_pinjam
 */
function loadBukuList() {
    const kodePinjam = document.getElementById('kodePinjam').value;
    const bukuContainer = document.getElementById('bukuContainer');
    const btnSubmit = document.getElementById('btnSubmit');
    
    if (!kodePinjam) {
        bukuContainer.innerHTML = `
            <div class="text-center text-muted py-5">
                <i class="bi bi-inbox display-3 text-muted mb-3"></i>
                <p>Pilih transaksi untuk menampilkan daftar buku...</p>
            </div>
        `;
        btnSubmit.disabled = true;
        return;
    }

    // Show loading
    bukuContainer.innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-success" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Memuat daftar buku...</p>
        </div>
    `;

    // AJAX call
    fetch(`{{ route('pengembalian.get-buku-list') }}?kode_pinjam=${encodeURIComponent(kodePinjam)}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderBukuList(data.data);
                btnSubmit.disabled = false;
            } else {
                bukuContainer.innerHTML = `
                    <div class="alert alert-warning" role="alert">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        ${data.message}
                    </div>
                `;
                btnSubmit.disabled = true;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            bukuContainer.innerHTML = `
                <div class="alert alert-danger" role="alert">
                    <i class="bi bi-exclamation-circle me-2"></i>
                    Gagal memuat daftar buku
                </div>
            `;
            btnSubmit.disabled = true;
        });
}

/**
 * Render table dengan checkbox kondisi buku
 */
function renderBukuList(data) {
    let html = `
        <div class="bg-light p-3 rounded mb-3 border-start border-success border-4">
            <div class="row g-2">
                <div class="col-md-6">
                    <div><strong class="text-muted">Siswa:</strong> ${data.siswa}</div>
                    <div><strong class="text-muted">Kelas:</strong> ${data.kelas}</div>
                </div>
                <div class="col-md-6">
                    <div><strong class="text-muted">Kode Pinjam:</strong> <code>${data.kode_pinjam}</code></div>
                    <div><strong class="text-muted">Tgl Pinjam:</strong> ${data.tanggal_pinjam}</div>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-success">
                    <tr>
                        <th width="50">#</th>
                        <th width="80">Kode</th>
                        <th>Judul Buku</th>
                        <th width="200">Kondisi Pengembalian</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
    `;

    data.buku.forEach((buku, index) => {
        html += `
            <tr>
                <td class="fw-bold">${index + 1}</td>
                <td><code class="bg-light p-2 rounded">${buku.kode}</code></td>
                <td>
                    <strong>${buku.judul}</strong><br>
                    <small class="text-muted">${buku.penulis}</small>
                </td>
                <td>
                    <div class="btn-group-vertical w-100" role="group">
                        <input type="radio" class="btn-check" name="buku_kondisi[${buku.buku_id}]" id="ok_${buku.buku_id}" value="ok" checked required>
                        <label class="btn btn-outline-success btn-sm" for="ok_${buku.buku_id}">
                            <i class="bi bi-check-circle me-1"></i>OK
                        </label>

                        <input type="radio" class="btn-check" name="buku_kondisi[${buku.buku_id}]" id="rusak_${buku.buku_id}" value="rusak">
                        <label class="btn btn-outline-warning btn-sm" for="rusak_${buku.buku_id}">
                            <i class="bi bi-exclamation-circle me-1"></i>RUSAK
                        </label>

                        <input type="radio" class="btn-check" name="buku_kondisi[${buku.buku_id}]" id="hilang_${buku.buku_id}" value="hilang">
                        <label class="btn btn-outline-danger btn-sm" for="hilang_${buku.buku_id}">
                            <i class="bi bi-x-circle me-1"></i>HILANG
                        </label>
                    </div>
                </td>
                <td>
                    <textarea name="buku_keterangan[${buku.buku_id}]" class="form-control form-control-sm" 
                              placeholder="Isi jika rusak/hilang (alasan, detail kerusakan, dll)" rows="2"></textarea>
                </td>
            </tr>
        `;
    });

    html += `
                </tbody>
            </table>
        </div>

        <div class="alert alert-info alert-sm">
            <i class="bi bi-info-circle me-2"></i>
            <strong>Catatan:</strong> 
            <ul class="mb-0 mt-2 small">
                <li>Pilih <strong>OK</strong> jika buku dikembalikan dalam kondisi baik (stok akan di-restore)</li>
                <li>Pilih <strong>RUSAK</strong> jika ada kerusakan (denda Rp 70.000, stok TIDAK restore)</li>
                <li>Pilih <strong>HILANG</strong> jika tidak dikembalikan (denda Rp 70.000, stok TIDAK restore)</li>
                <li>Isi keterangan untuk kondisi rusak/hilang</li>
            </ul>
        </div>
    `;

    document.getElementById('bukuContainer').innerHTML = html;
}
</script>
@endpush
