    @extends('layouts.app')
    @section('title','Peminjaman Buku')
    @section('content')
    <div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">

        <form method="GET">

            <div class="d-flex justify-content-between align-items-center mb-2">

                <h5 class="fw-bold mb-0">
                    <i class="bi bi-arrow-bar-up me-2"></i>
                    Peminjaman Buku
                </h5>

                <a href="{{ route('peminjaman.create') }}"
                   class="btn btn-primary btn-sm">

                    <i class="bi bi-plus-lg me-1"></i>
                    Catat Peminjaman

                </a>

            </div>

            <div class="row g-2 align-items-center">

                <!-- SEARCH -->
                <div class="col-md-5">

                    <input type="text"
                           name="search"
                           class="form-control form-control-sm"
                           placeholder="Cari kode pinjam / nama anggota..."
                           value="{{ request('search') }}">

                </div>

                <!-- STATUS -->
                <div class="col-md-3">

                    <select name="status"
                            class="form-select form-select-sm">

                        <option value="">
                            Semua Status
                        </option>

                        <option value="dipinjam"
                            {{ request('status') == 'dipinjam' ? 'selected' : '' }}>
                            Dipinjam
                        </option>

                        <option value="dikembalikan"
                            {{ request('status') == 'dikembalikan' ? 'selected' : '' }}>
                            Dikembalikan
                        </option>

                    </select>

                </div>

                <!-- BUTTON -->
                <div class="col-md-4 d-flex gap-1">

                    <button class="btn btn-primary btn-sm w-100">
                        Cari
                    </button>

                    <a href="{{ route('peminjaman.index') }}"
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
                        <th>No</th><th>Kode</th><th>Anggota</th><th>Buku</th>
                        <th>Tgl Pinjam</th><th>Tgl Kembali</th><th>Status</th><th>Aksi</th>
                    </tr>
                
                <tbody>
                @forelse($peminjamans as $kode => $items)

                @php
                    $p = $items->first();
                @endphp
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td><code>{{ $p->kode_pinjam }}</code></td>
                    <td>{{ $p->anggota->nama_siswa }}<br>
                        <small class="text-muted">{{ $p->anggota->kelas }}</small></td>
                        <td>
                            {{ $items->count() }} Buku
                            <br>
                        </td>
                    <td>{{ \Carbon\Carbon::parse($p->tanggal_pinjam)->format('d/m/Y') }}</td>
                    <td>{{ \Carbon\Carbon::parse($p->tanggal_kembali)->format('d/m/Y') }}</td>
                    <td>
                        @if($p->status=='dipinjam')
                            <span class="badge bg-warning text-dark">Dipinjam</span>
                        @elseif($p->status=='dikembalikan')
                            <span class="badge bg-success">Dikembalikan</span>
                        @else
                            <span class="badge bg-danger">Terlambat</span>
                        @endif
                    </td>
                    <td>
                        <button
                            class="btn btn-info btn-sm py-0 px-2"
                            data-bs-toggle="modal"
                            data-bs-target="#detailModal{{ $kode }}"
                        >
                            <i class="bi bi-eye"></i>
                        </button>
                        
                        @if($p->status=='dipinjam')
                        <a href="{{ route('pengembalian.create') }}?pid={{ $p->id }}"
                        class="btn btn-success btn-sm py-0 px-2" title="Kembalikan">
                            <i class="bi bi-arrow-bar-down"></i>
                        </a>
                        @endif
                        <form action="{{ route('peminjaman.destroy',$p) }}" method="POST" class="d-inline"
                            onsubmit="return confirm('Hapus data peminjaman ini?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-danger btn-sm py-0 px-2"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
                
                @empty
                <tr><td colspan="8" class="text-center text-muted py-4">Belum ada data peminjaman.</td></tr>
                
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @foreach($peminjamans as $kode => $items)

@php
    $p = $items->first();
@endphp

<div class="modal fade"
     id="detailModal{{ $kode }}"
     tabindex="-1"
     aria-hidden="true">

    <div class="modal-dialog modal-lg modal-dialog-centered">

        <div class="modal-content">

            <div class="modal-header">

                <h5 class="modal-title">
                    <i class="bi bi-journal-bookmark me-2"></i>
                    Detail Transaksi Peminjaman
                </h5>

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal">
                </button>

            </div>

            <div class="modal-body">

                <div class="row g-3 mb-4">

                    <div class="col-md-6">

                        <label class="fw-bold text-muted">
                            Kode Peminjaman
                        </label>

                        <div>
                            <code>{{ $p->kode_pinjam }}</code>
                        </div>

                    </div>

                    <div class="col-md-6">

                        <label class="fw-bold text-muted">
                            Status
                        </label>

                        <div>

                            @if($p->status=='dipinjam')

                                <span class="badge bg-warning text-dark">
                                    Dipinjam
                                </span>

                            @elseif($p->status=='dikembalikan')

                                <span class="badge bg-success">
                                    Dikembalikan
                                </span>

                            @else

                                <span class="badge bg-danger">
                                    Terlambat
                                </span>

                            @endif

                        </div>

                    </div>

                </div>

                <div class="row g-3 mb-4">

                    <div class="col-md-6">

                        <label class="fw-bold text-muted">
                            Nama Anggota
                        </label>

                        <div>
                            {{ $p->anggota->nama_siswa }}
                        </div>

                    </div>

                    <div class="col-md-6">

                        <label class="fw-bold text-muted">
                            Kelas
                        </label>

                        <div>
                            {{ $p->anggota->kelas }}
                        </div>

                    </div>

                </div>

                <div class="row g-3 mb-4">

                    <div class="col-md-6">

                        <label class="fw-bold text-muted">
                            Tanggal Pinjam
                        </label>

                        <div>
                            {{ \Carbon\Carbon::parse($p->tanggal_pinjam)->format('d/m/Y') }}
                        </div>

                    </div>

                    <div class="col-md-6">

                        <label class="fw-bold text-muted">
                            Jumlah Buku
                        </label>

                        <div>
                            {{ $items->count() }} Buku
                        </div>

                    </div>

                </div>

                <hr>

                <h6 class="fw-bold mb-3">
                    Daftar Buku Dipinjam
                </h6>

                <table class="table table-striped table-hover align-middle">

                    <thead class="table-light">

                        <tr>

                            <th width="60">
                                No
                            </th>

                            <th>
                                Judul Buku
                            </th>

                            <th width="150">
                                Kategori
                            </th>

                        </tr>

                    </thead>

                    <tbody>

                    @foreach($items as $item)

                    <tr>

                        <td>
                            {{ $loop->iteration }}
                        </td>

                        <td>
                            {{ $item->buku->judul_buku }}
                        </td>

                        <td>

                            <span class="badge bg-primary">

                                {{ $item->buku->kategori->nama_kategori ?? '-' }}

                            </span>

                        </td>

                    </tr>

                    @endforeach

                    </tbody>

                </table>

            </div>

            <div class="modal-footer">

                <button type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal">

                    Tutup

                </button>

            </div>

        </div>

    </div>

</div>

@endforeach
@endsection

    

    