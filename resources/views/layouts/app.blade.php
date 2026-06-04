<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'Perpustakaan SMPN 3 Legok')</title>

    {{-- Bootstrap CSS Offline --}}
    <link rel="stylesheet" href="{{ asset('assets/bootstrap/css/bootstrap.min.css') }}">

    {{-- Bootstrap Icons Offline (Cukup panggil yang .min.css) --}}
    <link rel="stylesheet" href="{{ asset('assets/bootstrap-icons/font/bootstrap-icons.min.css') }}">
    
    {{-- Bootstrap Tomselect Offline --}}
    <link rel="stylesheet" href="{{ asset('assets/tomselect/tom-select.css') }}">

    <style>
        :root{
            --sidebar-width:210px;
            --primary:#4F46E5;
            --primary-light:#EEF2FF;
            --bg:#F8FAFC;
            --card:#FFFFFF;
            --text:#1E293B;
            --muted:#64748B;
            --border:#E5E7EB;
            --success:#10B981;
            --danger:#EF4444;
            --warning:#F59E0B;
        }

        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
        }

        body{
            background:var(--bg);
            font-family:'Segoe UI',sans-serif;
            color:var(--text);
        }

        /* SIDEBAR */

        #sidebar{
            width:var(--sidebar-width);
            height:100vh;
            position:fixed;
            left:0;
            top:0;
            background:#fff;
            border-right:1px solid var(--border);
            padding:24px 16px;
            overflow-y:hidden;
            z-index:1000;
            transition:.2s;
        }

        #sidebar:hover{
            overflow-y:auto;
        }

        #sidebar::-webkit-scrollbar{
            width:6px;
        }

        #sidebar::-webkit-scrollbar-track{
            background:transparent;
        }

        #sidebar::-webkit-scrollbar-thumb{
            background:#CBD5E1;
            border-radius:20px;
        }

        #sidebar::-webkit-scrollbar-thumb:hover{
            background:#94A3B8;
        }

        .sidebar-logo{
            text-align:center;
            margin-bottom:35px;
        }

        .sidebar-icon{
            width:80px;
            height:80px;
            background:var(--primary-light);
            border-radius:24px;
            display:flex;
            align-items:center;
            justify-content:center;
            margin:auto;
        }

        .sidebar-icon i{
            font-size:35px;
            color:var(--primary);
        }

        .sidebar-school{
            margin-top:12px;
            font-size:14px;
            font-weight:700;
        }

        .sidebar-label{
            font-size:12px;
            color:#94A3B8;
            font-weight:700;
            margin:18px 0 10px;
            text-transform:uppercase;
        }

        #sidebar .nav-link{
            display:flex;
            align-items:center;
            gap:10px;
            padding:10px 12px;
            margin-bottom:5px;
            border-radius:12px;
            color:#475569;
            font-size:14px;
            transition:.2s;
        }

        #sidebar .nav-link:hover{
            background:#EEF2FF;
            color:#4F46E5;
        }

        #sidebar .nav-link.active{
            background:var(--primary-light);
            color:var(--primary);
        }

        #sidebar .nav-link i{
            font-size:16px;
        }

        /* CONTENT */

        #main-content{
            margin-left:var(--sidebar-width);
            min-height:100vh;
        }

        /* NAVBAR */

        .navbar-main{
            background:#fff;
            border-bottom:1px solid var(--border);
            padding:12px 24px;
            position:sticky;
            top:0;
            z-index:999;
        }

        .navbar-title{
            font-size:18px;
            font-weight:700;
            color:var(--text);
        }

        .profile-box{
            display:flex;
            align-items:center;
            gap:12px;
        }

        .profile-avatar{
            width:38px;
            height:38px;
            font-size:14px;
            box-shadow:0 4px 12px rgba(79,70,229,.25);
            border-radius:50%;
            background:var(--primary);
            color:white;
            display:flex;
            align-items:center;
            justify-content:center;
            font-weight:700;
        }

        .content-wrapper{
            padding:20px;
        }

        /* CARD */

        .card{
            border:none !important;
            border-radius:16px !important;
            background:#fff;
            border-top:3px solid #4F46E5 !important;
            box-shadow:
                0 4px 12px rgba(15,23,42,.04),
                0 12px 24px rgba(15,23,42,.04);
        }

        /* BUTTON */

        .btn{
            border-radius:14px !important;
        }

        .btn-primary{
            background:var(--primary);
            border:none;
        }

        .btn-primary:hover{
            background:#4338CA;
        }

        /* TABLE */

        .table{
            margin-bottom:0;
            border-collapse:separate;
            border-spacing:0;
        }

        .table thead th{
            background:#EEF2FF;
            color:#4F46E5;
            font-weight:700;
        }

        .table tbody tr:hover{
            background:#F8FAFC;
        }

        .table td{
            vertical-align:middle;
        }

        /* css stat */
        .stat-card{
            border-radius:18px;
            padding:20px;
            color:white;
            position:relative;
            overflow:hidden;
        }

        .stat-card::after{
            content:'';
            position:absolute;
            right:-15px;
            top:-15px;
            width:80px;
            height:80px;
            background:rgba(255,255,255,.15);
            border-radius:50%;
        }

        .stat-num{
            font-size:28px;
            font-weight:700;
        }

        .stat-label{
            font-size:13px;
            opacity:.9;
        }

        /* BADGE */
        .badge{
            border-radius:10px;
            padding:7px 10px;
            font-weight:500;
        }

        .bg-buku      { background: linear-gradient(135deg, #3b82f6, #1d4ed8); }
        .bg-kategori  { background: linear-gradient(135deg, #06b6d4, #0e7490); }
        .bg-anggota   { background: linear-gradient(135deg, #10b981, #047857); }
        .bg-pinjam    { background: linear-gradient(135deg, #ef4444, #b91c1c); }

        /* ALERT */

        .alert{
            border-radius:18px;
        }
        

        /* INFO BOX */

        .info-rules{
            background:#fff;
            border-radius:18px;
            padding:20px;
            box-shadow:
                0 4px 12px rgba(15,23,42,.04);
        }
    </style>
    @stack('styles')
</head>
    <body>
        <div id="sidebar">
        <!-- LOGO -->
        <div class="sidebar-logo">
            <div class="sidebar-icon">
                <i class="bi bi-book-half"></i>
            </div>
            <div class="sidebar-school">
                SMPN 3 Legok
            </div>
        </div>
        <!-- MAIN -->
        <div class="sidebar-label">Main Menu</div>
        <a href="{{ route('home') }}"
        class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}">
            <i class="bi bi-speedometer2"></i>
            Dashboard
        </a>
        <!-- MASTER DATA -->
        <div class="sidebar-label">Master Data</div>

        <!-- BUKU -->
        <a class="nav-link d-flex justify-content-between align-items-center
            {{ request()->routeIs('buku.*') ? 'active' : '' }}"
            data-bs-toggle="collapse"
            href="#menuBuku">
            <span><i class="bi bi-book"></i> Buku</span>
            <i class="bi bi-chevron-down"></i>
        </a>
        <div class="collapse ps-3 {{ request()->routeIs('buku.*') || request()->routeIs('kategori.*') ? 'show' : '' }}" id="menuBuku">
            <a href="{{ route('kategori.index') }}"
            class="nav-link {{ request()->routeIs('kategori.*') ? 'active' : '' }}">
                Kategori Buku
            </a>
            <a href="{{ route('buku.index') }}"
            class="nav-link {{ request()->routeIs('buku.index') ? 'active' : '' }}">
                Daftar Buku
            </a>
        </div>
        <!-- ANGGOTA -->
        <a class="nav-link d-flex justify-content-between align-items-center
            {{ request()->routeIs('anggota.*') ? 'active' : '' }}"
            data-bs-toggle="collapse"
            href="#menuAnggota">
            <span><i class="bi bi-people"></i> Anggota</span>
            <i class="bi bi-chevron-down"></i>
        </a>
        <div class="collapse ps-3 {{ request()->routeIs('anggota.*') ? 'show' : '' }}" id="menuAnggota">
            <a href="{{ route('anggota.index') }}"
            class="nav-link {{ request()->routeIs('anggota.index') ? 'active' : '' }}">
                Data Anggota
            </a>
        </div>
        <!-- TRANSAKSI -->
        <div class="sidebar-label">Transaksi</div>
        <!-- PEMINJAMAN -->
        <a class="nav-link d-flex justify-content-between align-items-center
            {{ request()->routeIs('peminjaman.*') ? 'active' : '' }}"
            data-bs-toggle="collapse"
            href="#menuPinjam">
            <span><i class="bi bi-journal-arrow-up"></i> Peminjaman</span>
            <i class="bi bi-chevron-down"></i>
        </a>
        <div class="collapse ps-3 {{ request()->routeIs('peminjaman.*') ? 'show' : '' }}" id="menuPinjam">
            <a href="{{ route('peminjaman.index') }}"
            class="nav-link {{ request()->routeIs('peminjaman.index') ? 'active' : '' }}">
                Data Peminjaman
            </a>
        </div>
        <!-- PENGEMBALIAN -->
        <a class="nav-link d-flex justify-content-between align-items-center
            {{ request()->routeIs('pengembalian.*') ? 'active' : '' }}"
            data-bs-toggle="collapse"
            href="#menuKembali">
            <span><i class="bi bi-journal-arrow-down"></i> Pengembalian</span>
            <i class="bi bi-chevron-down"></i>
        </a>
        <div class="collapse ps-3 {{ request()->routeIs('pengembalian.*') ? 'show' : '' }}" id="menuKembali">
            <a href="{{ route('pengembalian.index') }}"
            class="nav-link {{ request()->routeIs('pengembalian.index') ? 'active' : '' }}">
                Data Pengembalian
            </a>
        </div>
        <!-- MONITORING (BARU - INTI SISTEM) -->
        <div class="sidebar-label">Monitoring</div>
        <a href="#"
        class="nav-link">
            <i class="bi bi-book"></i>
            Buku Di Pinjam
        </a>
        <a href="#"
        class="nav-link">
            <i class="bi bi-exclamation-triangle"></i>
            Buku Terlambat
        </a>
        <!-- LAPORAN -->
        <div class="sidebar-label">Laporan</div>
        <a href="#"
        class="nav-link">
            <i class="bi bi-file-earmark-text"></i>
            Laporan Peminjaman
        </a>
        <a href="#"
        class="nav-link">
            <i class="bi bi-file-earmark-bar-graph"></i>
            Laporan Pengembalian
        </a>
        <hr>
        <!-- LOGOUT -->
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="nav-link border-0 bg-transparent w-100 text-start d-flex align-items-center">
                <i class="bi bi-box-arrow-left"></i>
                Logout
            </button>
        </form>
    </div>
        <div id="main-content">

            <nav class="navbar navbar-main">

                <div>

                    <div class="navbar-title">
                        @yield('title')
                    </div>

                    <small class="text-muted">
                        Sistem Perpustakaan SMPN 3 Legok
                    </small>

                </div>

                <div class="ms-auto profile-box">

                    <div class="text-end">

                        <div class="fw-bold">
                            {{ auth()->user()->name }}
                        </div>

                        <small class="text-muted">
                            Administrator
                        </small>

                    </div>

                    <div class="profile-avatar">
                        {{ strtoupper(substr(auth()->user()->name,0,1)) }}
                    </div>

                </div>

            </nav>

            <div class="content-wrapper">

                @if(session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $e)
                                <li>{{ $e }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')

            </div>

        </div>
        {{-- Bootstrap JS Lokal --}}
        <script src="{{ asset('assets/bootstrap/js/bootstrap.bundle.min.js') }}"></script>

        <script src="{{ asset('assets/tomselect/tom-select.complete.min.js') }}"></script>
        @stack('scripts')
    </body>
</html>