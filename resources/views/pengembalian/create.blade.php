@extends('layouts.app')
@section('title','Catat Pengembalian')
@section('content')
<div class="card border-0 shadow-sm" style="max-width:600px">
    <div class="card-header bg-success text-white fw-bold">
        <i class="bi bi-arrow-bar-down me-2"></i>Catat Pengembalian Buku
    </div>
    <div class="card-body">
        <div class="alert alert-info py-2 small">
            <i class="bi bi-info-circle me-1"></i>
            Denda otomatis dihitung: <strong>Rp 15.000 per minggu</strong> keterlambatan
        </div>
        <form action="{{ route('pengembalian.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label class="form-label fw-semibold">Data Peminjaman</label>
                <select name="peminjaman_id" class="form-select" required>
                    <option value="">-- Pilih Peminjaman --</option>
                    @foreach($peminjamans as $pm)
                    <option value="{{ $pm->id }}"
                            {{ request('pid')==$pm->id || old('peminjaman_id')==$pm->id ? 'selected' : '' }}>
                        {{ $pm->anggota->nama_siswa }} — {{ $pm->buku->judul_buku }}
                        (Batas: {{ \Carbon\Carbon::parse($pm->tanggal_kembali)->format('d/m/Y') }})
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Tanggal Pengembalian Aktual</label>
                <input type="date" name="tanggal_kembali_aktual" class="form-control"
                       value="{{ old('tanggal_kembali_aktual', date('Y-m-d')) }}" required>
            </div>
            <div class="mb-4">
                <label class="form-label fw-semibold">Keterangan</label>
                <textarea name="keterangan" class="form-control" rows="2"
                          placeholder="kondisi buku, catatan, dll">{{ old('keterangan') }}</textarea>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-success">
                    <i class="bi bi-save me-1"></i>Simpan Pengembalian
                </button>
                <a href="{{ route('pengembalian.index') }}" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection