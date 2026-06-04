@extends('layouts.app')
@section('title','Tambah Buku')
@section('content')
<div class="card border-0 shadow-sm" style="max-width:700px">
    <div class="card-header bg-primary text-white fw-bold">
        <i class="bi bi-plus-lg me-2"></i>Tambah Buku
    </div>
    <div class="card-body">
        <form action="{{ route('buku.store') }}" method="POST">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Kode Buku</label>
                    <input type="text" name="kode_buku" class="form-control" value="{{ old('kode_buku') }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Kategori</label>
                    <select name="kategori_id" class="form-select" required>
                        <option value=""> Pilih Kategori </option>
                        @foreach($kategoris as $k)
                        <option value="{{ $k->id }}" {{ old('kategori_id')==$k->id ? 'selected' : '' }}>
                            {{ $k->nama_kategori }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Judul Buku</label>
                    <input type="text" name="judul_buku" class="form-control" value="{{ old('judul_buku') }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Pengarang</label>
                    <input type="text" name="pengarang" class="form-control" value="{{ old('pengarang') }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Penerbit</label>
                    <input type="text" name="penerbit" class="form-control" value="{{ old('penerbit') }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Tahun Terbit</label>
                    <input type="number" name="tahun_terbit" class="form-control" value="{{ old('tahun_terbit', date('Y')) }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Kelas</label>
                    <select name="kelas" class="form-select" required>
                        <option value="">-- Pilih --</option>
                        @foreach(['VII','VIII','IX'] as $kls)
                        <option value="{{ $kls }}" {{ old('kelas')==$kls ? 'selected' : '' }}>{{ $kls }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Jumlah Buku</label>
                    <input type="number" name="jumlah_buku" class="form-control" value="{{ old('jumlah_buku',1) }}" min="1" required>
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Mata Pelajaran</label>
                    <input type="text" name="mata_pelajaran" class="form-control" value="{{ old('mata_pelajaran') }}" required>
                </div>
            </div>
            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save me-1"></i>Simpan
                </button>
                <a href="{{ route('buku.index') }}" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection