@extends('layouts.app')
@section('title','Pengembalian Buku')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0"><i class="bi bi-arrow-bar-down me-2"></i>Pengembalian Buku</h5>
    <a href="{{ route('pengembalian.create') }}" class="btn btn-success btn-sm">
        <i class="bi bi-plus-lg me-1"></i>Catat Pengembalian
    </a>
</div>
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-dark">
                <tr>
                    <th>#</th><th>Anggota</th><th>Buku</th>
                    <th>Tgl Dikembalikan</th><th>Denda</th><th>Status</th>
                </tr>
            </thead>
            <tbody>
            @forelse($pengembalians as $p)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $p->peminjaman->anggota->nama_siswa }}</td>
                <td>{{ $p->peminjaman->buku->judul_buku }}</td>
                <td>{{ \Carbon\Carbon::parse($p->tanggal_kembali_aktual)->format('d/m/Y') }}</td>
                <td>
                    @if($p->denda > 0)
                        <span class="text-danger fw-bold">Rp {{ number_format($p->denda,0,',','.') }}</span>
                    @else
                        <span class="text-success">-</span>
                    @endif
                </td>
                <td>
                    @if($p->denda > 0)
                        <span class="badge bg-danger">Terlambat</span>
                    @else
                        <span class="badge bg-success">Tepat Waktu</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="text-center text-muted py-4">Belum ada pengembalian.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $pengembalians->links() }}</div>
@endsection