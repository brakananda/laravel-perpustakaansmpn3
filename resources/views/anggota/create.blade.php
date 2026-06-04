@extends('layouts.app')
@section('title','Tambah Anggota')
@section('content')
<div class="card border-0 shadow-sm" style="max-width:600px">
    <div class="card-header bg-primary text-white fw-bold">
        <i class="bi bi-person-plus me-2"></i>Tambah Anggota
    </div>
    <div class="card-body">
        <form action="{{ route('anggota.store') }}" method="POST">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">NIS</label>
                    <input type="text" name="nis" class="form-control" value="{{ old('nis') }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Kelas</label>
                        <select name="kelas" class="form-select" required> 
                            <option value="">Pilih Kelas</option>
                            <option value="VII"{{ old('kelas')=='VII' ? 'selected' : '' }}> VII</option>
                            <option value="VIII"{{ old('kelas')=='VIII' ? 'selected' : '' }}>VIII</option>
                            <option value="IX"{{ old('kelas')=='IX' ? 'selected' : '' }}>IX</option>
                        </select>
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Nama Siswa</label>
                    <input type="text" name="nama_siswa" class="form-control" value="{{ old('nama_siswa') }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Jenis Kelamin</label>
                    <select name="jenis_kelamin" class="form-select" required>
                        <option value="">Pilih Jenis Kelamin</option>
                        <option value="L"{{ old('jenis_kelamin')=='L' ? 'selected' : '' }}>Laki-laki</option>
                        <option value="P"{{ old('jenis_kelamin')=='P' ? 'selected' : '' }}>Perempuan</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">No. Telp</label>
                    <input type="number" name="no_telp" class="form-control" value="{{ old('no_telp') }}">
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Alamat</label>
                    <textarea name="alamat" class="form-control" rows="2">{{ old('alamat') }}</textarea>
                </div>
            </div>
            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary">Simpan</button>
                <a href="{{ route('anggota.index') }}" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection