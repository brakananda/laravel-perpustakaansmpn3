@extends('layouts.app')

@section('title','Catat Peminjaman')

@section('content')

<div class="card border-0 shadow-sm" style="max-width:800px">

    <div class="card-header bg-primary text-white fw-bold">
        <i class="bi bi-journal-arrow-up me-2"></i>
        Catat Peminjaman Buku
    </div>

    <div class="card-body">

        <form action="{{ route('peminjaman.store') }}" method="POST">
            @csrf

            {{-- SISWA --}}
            <div class="mb-3">
                <label class="form-label fw-semibold">Cari Siswa</label>

                <select id="anggotaSelect" name="anggota_id" class="form-select" required>
                    <option value="">Ketik Nama atau NIS...</option>

                    @foreach($anggotas as $a)
                        <option value="{{ $a->id }}" {{ old('anggota_id')==$a->id ? 'selected' : '' }}>
                            {{ $a->nama_siswa }} - {{ $a->nis }} - {{ $a->kelas }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- KATEGORI --}}
            <div class="mb-3">
                <label class="form-label fw-semibold">Kategori Buku</label>

                <select id="kategoriSelect" class="form-select">
                    <option value="">Pilih Kategori</option>

                    @foreach($kategoris as $k)
                        <option value="{{ $k->id }}">
                            {{ $k->nama_kategori }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- LIST BUKU --}}
            <div id="daftarBuku"
                 class="border rounded p-3"
                 style="display:none; max-height:350px; overflow:auto;">

                <div class="d-flex justify-content-between mb-3">
                    <strong>Daftar Buku</strong>

                    <button type="button"
                            id="pilihSemua"
                            class="btn btn-sm btn-outline-primary">
                        Pilih Semua
                    </button>
                </div>

                @foreach($bukus as $b)
                    <div class="form-check buku-item"
                         data-kategori="{{ $b->kategori_id }}"
                         style="display:none;">

                        <input class="form-check-input"
                               type="checkbox"
                               name="buku_ids[]"
                               value="{{ $b->id }}"
                               id="buku{{ $b->id }}">

                        <label class="form-check-label" for="buku{{ $b->id }}">
                            <strong>{{ $b->judul_buku }}</strong><br>
                            <small class="text-muted">
                                Kelas {{ $b->kelas }} | Stok {{ $b->jumlah_tersedia }}
                            </small>
                        </label>

                    </div>
                @endforeach

            </div>

            {{-- TANGGAL --}}
            <div class="mb-4 mt-3">

                <label class="form-label fw-semibold">
                    Tanggal Peminjaman
                </label>

                <input
                    type="text"
                    class="form-control"
                    value="{{ now()->format('d F Y') }}"
                    readonly>

                <input
                    type="hidden"
                    name="tanggal_pinjam"
                    value="{{ now()->format('Y-m-d') }}">

                <small class="text-muted">
                    Tanggal mengikuti hari saat transaksi dilakukan
                </small>

            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save me-1"></i> Simpan Peminjaman
                </button>

                <a href="{{ route('peminjaman.index') }}" class="btn btn-secondary">
                    Batal
                </a>
            </div>

        </form>

    </div>
</div>

@endsection

@push('scripts')

<script>
new TomSelect('#anggotaSelect', {
    create: false,
    placeholder: 'Cari Nama Siswa atau NIS'
});

const kategoriSelect = document.getElementById('kategoriSelect');
const daftarBuku = document.getElementById('daftarBuku');

kategoriSelect.addEventListener('change', function () {

    let kategoriId = this.value;

    daftarBuku.style.display = kategoriId ? 'block' : 'none';

    document.querySelectorAll('.buku-item').forEach(function (item) {

        let match = item.dataset.kategori === kategoriId;

        item.style.display = match ? 'block' : 'none';

        if (!match) {
            item.querySelector('input').checked = false;
        }
    });
});

document.getElementById('pilihSemua').addEventListener('click', function () {

    document.querySelectorAll('.buku-item').forEach(function (item) {

        if (item.style.display === 'block') {
            item.querySelector('input').checked = true;
        }
    });
});
</script>

@endpush