<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password</title>

    <link rel="stylesheet" href="{{ asset('assets/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet"
          href="{{ asset('assets/bootstrap/bootstrap-icons/fonts/bootstrap-icons.css') }}">

    <style>
        body{
            background:#2979FF;
            min-height:100vh;
            display:flex;
            justify-content:center;
            align-items:center;
        }

        .card-custom{
            width:500px;
            border-radius:16px;
            overflow:hidden;
            box-shadow:0 20px 60px rgba(0,0,0,.25);
        }

        .card-header-custom{
            background:#1565C0;
            color:white;
            text-align:center;
            padding:25px;
        }

        .card-body-custom{
            background:white;
            padding:30px;
        }
    </style>
</head>
<body>

<div class="card-custom">

    <div class="card-header-custom">
        <i class="bi bi-shield-lock-fill" style="font-size:2.5rem"></i>
        <h4 class="mt-2 mb-0">Lupa Password</h4>
    </div>

    <div class="card-body-custom">

        <div class="alert alert-warning">

            <strong>Informasi:</strong><br><br>

            Aplikasi perpustakaan ini berjalan secara offline dan tidak
            menyediakan reset password otomatis.

            <hr>

            Jika Anda lupa password, silakan hubungi administrator
            atau pihak yang bertanggung jawab atas pengelolaan sistem
            untuk mendapatkan bantuan.

        </div>

        <a href="{{ route('login') }}"
           class="btn btn-primary w-100">

            <i class="bi bi-arrow-left-circle"></i>
            Kembali ke Login

        </a>

    </div>

</div>

</body>
</html>