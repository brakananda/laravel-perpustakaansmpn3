@extends('layouts.app')
@section('title','Kategori Buku')
@section('content')
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h5 class="fw-bold mb-0"><i class="bi bi-book me-2"></i>Kategori Buku</h5>
                <a href="{{ route('kategori.create') }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-lg me-1"></i>Tambah Kategori
                </a>
            </div>
        </form>
     </div>
</div>

<div class="row g-3">
    @forelse($kategoris as $k)
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold text-white"
                         style="width:50px;height:50px;font-size:1rem;
                                background:{{ $k->nama_kategori=='K13' ? '#1E88E5' : '#43A047' }}">
                        {{ substr($k->nama_kategori,0,2) }}
                    </div>
                    <div>
                        <div class="fw-bold">{{ $k->nama_kategori }}</div>
                        <div class="text-muted small">{{ $k->keterangan }}</div>
                        <div class="text-primary fw-semibold small">
                            {{ $k->bukus_sum_jumlah_buku ?? 0 }} buku ({{ $k->bukus_count }} judul)
                        </div>
                    </div>
                </div>
                <div class="d-flex gap-1">
                    <a href="{{ route('kategori.edit',$k) }}" class="btn btn-warning btn-sm py-0 px-2">
                        <i class="bi bi-pencil"></i>
                    </a>
                    <form action="{{ route('kategori.destroy',$k) }}" method="POST"
                          onsubmit="return confirm('Hapus kategori ini?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-danger btn-sm py-0 px-2"><i class="bi bi-trash"></i></button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @empty
    <p class="text-muted">Belum ada kategori.</p>
    @endforelse
</div>
@endsection