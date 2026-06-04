@extends('layouts.app')
@section('title','Edit Anggota')
@section('content')
<div class="card border-0 shadow-sm mx-auto" style="max-width:700px">
    <div class="card-header bg-warning fw-bold">
        <i class="bi bi-pencil-square me-2"></i>
        Edit Data Anggota
    </div>

    <div class="card-body">
        <form action="{{ route('anggota.update',$anggota) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">
                        NIS
                    </label>
                    <input
                        type="number"
                        name="nis"
                        class="form-control"
                        value="{{ old('nis',$anggota->nis) }}"
                        required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">
                        Kelas
                    </label>
                    <select name="kelas" class="form-select" required>
                        <option value="VII"
                            {{ $anggota->kelas=='VII' ? 'selected' : '' }}>
                            VII
                        </option>
                        <option value="VIII"
                            {{ $anggota->kelas=='VIII' ? 'selected' : '' }}>
                            VIII
                        </option>
                        <option value="IX"
                            {{ $anggota->kelas=='IX' ? 'selected' : '' }}>
                            IX
                        </option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">
                        Nama Siswa
                    </label>
                    <input
                        type="text"
                        name="nama_siswa"
                        class="form-control"
                        value="{{ old('nama_siswa',$anggota->nama_siswa) }}"
                        required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">
                        Jenis Kelamin
                    </label>
                    <select
                        name="jenis_kelamin"
                        class="form-select"
                        required>
                        <option value="L"
                            {{ $anggota->jenis_kelamin=='L' ? 'selected' : '' }}>
                            Laki-laki
                        </option>
                        <option value="P"
                            {{ $anggota->jenis_kelamin=='P' ? 'selected' : '' }}>
                            Perempuan
                        </option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">
                        No. Telepon
                    </label>
                    <input
                        type="text"
                        name="no_telp"
                        class="form-control"
                        value="{{ old('no_telp',$anggota->no_telp) }}"
                        placeholder="08xxxxxxxxxx">
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">
                        Alamat
                    </label>
                    <textarea
                        name="alamat"
                        rows="3"
                        class="form-control">{{ old('alamat',$anggota->alamat) }}</textarea>
                </div>
            </div>
            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-warning">
                    <i class="bi bi-save me-1"></i>
                    Update
                </button>
                <a href="{{ route('anggota.index') }}"
                class="btn btn-secondary">
                    Kembali
                </a>
            </div>
        </form>
    </div>
</div>
@endsection