@extends('layouts.app')
@section('title','Data Buku')
@section('content')
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h5 class="fw-bold mb-0"><i class="bi bi-book me-2"></i>Data Buku</h5>
                <a href="{{ route('buku.create') }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-lg me-1"></i>Tambah Buku
                </a>
            </div>
            <div class="row g-2 align-items-center">

                <!-- SEARCH -->
                <div class="col-md-4">
                    <input type="text"
                           name="search"
                           class="form-control form-control-sm"
                           placeholder="Cari buku..."
                           value="{{ request('search') }}">
                </div>

                <!-- KATEGORI -->
                <div class="col-md-3">
                    <select name="kategori" class="form-select form-select-sm">
                        <option value="">Kategori</option>
                        @foreach($kategoris as $k)
                            <option value="{{ $k->id }}"
                                {{ request('kategori') == $k->id ? 'selected' : '' }}>
                                {{ $k->nama_kategori }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- KELAS -->
                <div class="col-md-3">
                    <select name="kelas" class="form-select form-select-sm">
                        <option value="">Kelas</option>
                        @foreach(['VII','VIII','IX'] as $kls)
                            <option value="{{ $kls }}"
                                {{ request('kelas') == $kls ? 'selected' : '' }}>
                                {{ $kls }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- BUTTON -->
                <div class="col-md-2 d-flex gap-1">
                    <button class="btn btn-primary btn-sm w-100">
                        Cari
                    </button>
                    <a href="{{ route('buku.index') }}"
                       class="btn btn-outline-secondary btn-sm w-100">
                        Reset
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>
<div class="card border-0 shadow-sm">
    <div class="card-body p-2">
        <table class="table table-hover mb-4">
                <tr>
                    <th>No</th><th>Kode</th><th>Judul Buku</th><th>Kategori</th>
                    <th>Kelas</th><th>Mata Pelajaran</th><th>Stok</th><th>Aksi</th>
                </tr>
            <tbody>
            @forelse($bukus as $b)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td><code>{{ $b->kode_buku }}</code></td>
                <td>{{ $b->judul_buku }}</td>
                <td>
                    <span class="badge {{ $b->kategori->nama_kategori=='K13' ? 'bg-primary' : 'bg-success' }}">
                        {{ $b->kategori->nama_kategori }}
                    </span>
                </td>
                <td>{{ $b->kelas }}</td>
                <td>{{ $b->mata_pelajaran }}</td>
                <td>
                    <span class="badge bg-{{ $b->jumlah_tersedia>0 ? 'info' : 'danger' }} text-dark">
                        {{ $b->jumlah_tersedia }}/{{ $b->jumlah_buku }}
                    </span>
                </td>
                <td>
                    <a href="{{ route('buku.edit',$b) }}" class="btn btn-warning btn-sm py-0 px-2">
                        <i class="bi bi-pencil"></i>
                    </a>
                    <form action="{{ route('buku.destroy',$b) }}" method="POST" class="d-inline"
                          onsubmit="return confirm('Hapus buku ini?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-danger btn-sm py-0 px-2"><i class="bi bi-trash"></i></button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="8" class="text-center text-muted py-4">Belum ada data buku.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $bukus->links() }}</div>
@endsection