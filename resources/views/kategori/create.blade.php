@extends('layouts.app')
@section('title','Tambah Kategori')
@section('content')
<div class="card border-0 shadow-sm" style="max-width:450px">
    <div class="card-header bg-primary text-white fw-bold">Tambah Kategori</div>
    <div class="card-body">
        <form action="{{ route('kategori.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label class="form-label fw-semibold">Nama Kategori</label>
                <input type="text" name="nama_kategori" class="form-control"
                       placeholder="K13 / Merdeka" value="{{ old('nama_kategori') }}" required>
            </div>
            <div class="mb-4">
                <label class="form-label fw-semibold">Keterangan</label>
                <textarea name="keterangan" class="form-control" rows="3">{{ old('keterangan') }}</textarea>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Simpan</button>
                <a href="{{ route('kategori.index') }}" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection