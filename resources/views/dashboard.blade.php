@extends('layouts.app')
@section('title','Dashboard')
@section('content')
<h4 class="fw-bold mb-4">Selamat Datang, {{ auth()->user()->name }}</h4>

{{-- STAT CARDS --}}
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="stat-card bg-buku">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-label">JUMLAH BUKU</div>
                    <div class="stat-num">{{ $totalBuku }}</div>
                    <a href="{{ route('buku.index') }}" class="text-white-50 small">Lihat</a>
                </div>
                <i class="bi bi-book" style="font-size:2.2rem;opacity:.6"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card bg-kategori">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-label">KATEGORI</div>
                    <div class="stat-num">{{ $totalKategori }}</div>
                    <a href="{{ route('kategori.index') }}" class="text-white-50 small">Lihat</a>
                </div>
                <i class="bi bi-tag" style="font-size:2.2rem;opacity:.6"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card bg-anggota">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-label">ANGGOTA</div>
                    <div class="stat-num">{{ $totalAnggota }}</div>
                    <a href="{{ route('anggota.index') }}" class="text-white-50 small">Lihat</a>
                </div>
                <i class="bi bi-people" style="font-size:2.2rem;opacity:.6"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card bg-pinjam">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-label">PINJAMAN SAAT INI</div>
                    <div class="stat-num">{{ $totalPinjam }}</div>
                    <a href="{{ route('peminjaman.index') }}" class="text-white-50 small">Lihat</a>
                </div>
                <i class="bi bi-person-check" style="font-size:2.2rem;opacity:.6"></i>
            </div>
        </div>
    </div>
</div>

{{-- BUKU PER KATEGORI --}}
<div class="row g-3 mb-4">
    @foreach($bukuPerKategori as $kat)
    <div class="col-md-6">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center"
                         style="width:50px;height:50px;background:{{ $kat->nama_kategori=='K13' ? '#1E88E5' : '#43A047' }};color:#fff;font-size:1.1rem;font-weight:700">
                        {{ substr($kat->nama_kategori,0,2) }}
                    </div>
                    <div>
                        <div class="fw-bold">{{ $kat->nama_kategori }}</div>
                        <div class="text-muted small">{{ $kat->keterangan }}</div>
                        <div class="fw-semibold">{{ $kat->bukus_sum_jumlah_buku ?? 0 }} buku
                            <span class="text-muted fw-normal">({{ $kat->bukus_count }} judul)</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- INFO ATURAN --}}
<div class="info-rules">
    <div class="fw-bold mb-2"><i class="bi bi-info-circle me-2"></i>Syarat Anggota Peminjam Buku</div>
    <ol>
        <li></li>
</div>
@endsection