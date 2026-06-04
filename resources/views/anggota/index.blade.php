@extends('layouts.app')
@section('title','Data Anggota')
@section('content')
{{-- FILTER & SEARCH --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <h5 class="fw-bold mb-0"><i class="bi bi-book me-2"></i>Data Anggota</h5>
                <a href="{{ route('anggota.create') }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-lg me-1"></i>Tambah Anggota
                </a>
            </div>
            <div class="row g-2 align-items-center">
                <div class="col-md-4">
                    <input type="text"
                           name="search"
                           class="form-control"
                           placeholder="Cari NIS atau Nama Siswa..."
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select name="kelas" class="form-select">
                        <option value="">Semua Kelas</option>

                        <option value="VII"
                            {{ request('kelas') == 'VII' ? 'selected' : '' }}>
                            VII
                        </option>

                        <option value="VIII"
                            {{ request('kelas') == 'VIII' ? 'selected' : '' }}>
                            VIII
                        </option>

                        <option value="IX"
                            {{ request('kelas') == 'IX' ? 'selected' : '' }}>
                            IX
                        </option>
                    </select>
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search me-1"></i>Cari
                    </button>
                    <a href="{{ route('anggota.index') }}"
                       class="btn btn-secondary">
                        Reset
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>
{{-- TABEL --}}
<div class="card border-0 shadow-sm">
    <div class="card-body p-2">
        <table class="table table-hover mb-4">
                    <th width="60">No</th>
                    <th>NIPD</th>
                    <th>Nama Siswa</th>
                    <th>Kelas</th>
                    <th>Jenis Kelamin</th>
                    <th>No. Telp</th>
                    <th width="120">Aksi</th>
            </thead>
            <tbody>
            @forelse($anggotas as $a)
            <tr>
                <td>
                    {{ ($anggotas->currentPage()-1) * $anggotas->perPage() + $loop->iteration }}
                </td>
                <td>
                    <span class="fw-semibold">
                        {{ $a->nis }}
                    </span>
                </td>

                <td>
                    {{ $a->nama_siswa }}
                </td>

                <td>
                    <span class="badge bg-secondary">
                        {{ $a->kelas }}
                    </span>
                </td>

                <td>
                    {{ $a->jenis_kelamin }}
                </td>
                <td>
                    {{ $a->no_telp ?: '-' }}
                </td>
                <td>
                    <a href="{{ route('anggota.edit',$a) }}"
                       class="btn btn-warning btn-sm py-0 px-2"
                       title="Edit">

                        <i class="bi bi-pencil"></i>

                    </a>
                    <form action="{{ route('anggota.destroy',$a) }}"
                          method="POST"
                          class="d-inline"
                          onsubmit="return confirm('Hapus anggota ini?')">

                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger btn-sm py-0 px-2"
                                title="Hapus">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7"
                    class="text-center text-muted py-4">
                    Belum ada data anggota.
                </td>
            </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">
    {{ $anggotas->links() }}
</div>
@endsection