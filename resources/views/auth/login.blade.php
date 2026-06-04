<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Perpustakaan SMPN 3 Legok</title>
    {{-- Bootstrap CSS Offline --}}
    <link rel="stylesheet" href="{{ asset('assets/bootstrap/css/bootstrap.min.css') }}">
    {{-- Bootstrap Icons Offline --}}
    <link rel="stylesheet"
        href="{{ asset('assets/bootstrap/bootstrap-icons/fonts/bootstrap-icons.css') }}">
    {{-- Bootstrap Icons Offline --}}
    <link rel="stylesheet"
        href="{{ asset('assets/bootstrap/bootstrap-icons/fonts/bootstrap-icons.min.css') }}">
    <style>
        body { background: #2979FF; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .login-card { width: 420px; border-radius: 16px; overflow: hidden; box-shadow: 0 20px 60px rgba(0,0,0,.25); }
        .login-header { background: #1565C0; padding: 28px; text-align: center; color: #fff; }
        .login-body { background: #fff; padding: 32px; }
        .form-control:focus { border-color: #2979FF; box-shadow: 0 0 0 .2rem rgba(41,121,255,.2); }
        .btn-login { background: #2979FF; border: none; width: 100%; padding: 10px; font-weight: 600; font-size: 1rem; }
        .btn-login:hover { background: #1565C0; }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-header">
            <i class="bi bi-book-half" style="font-size:2.5rem"></i>
            <h4 class="mt-2 mb-0 fw-bold">SIPERGOK</h4>
            <small class="opacity-75">Sistem Perpustakaan SMPN 3 Legok</small>
        </div>
        <div class="login-body">
            <h5 class="fw-bold mb-4 text-center text-muted">Login Admin</h5>
            @if($errors->any())
            <div class="alert alert-danger py-2">
                <i class="bi bi-exclamation-circle me-1"></i>{{ $errors->first() }}
            </div>
            @endif
            <form method="POST" action="{{ route('login.post') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label fw-semibold">Email</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                        <input type="email" name="email" class="form-control"
                               placeholder="admin@perpus.com" value="{{ old('email') }}" required>
                    </div>
                </div>
                <div class="mb-4">
                    <label class="form-label fw-semibold">Password</label>
                    <!-- <div class="input-group">  
                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                        <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                    </div> -->
                    <div class="input-group">

                        <span class="input-group-text">
                            <i class="bi bi-lock"></i>
                        </span>

                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="form-control"
                            placeholder="••••••••"
                            required>

                        <button
                            class="btn btn-outline-secondary"
                            type="button"
                            id="togglePassword">

                            <i class="bi bi-eye"></i>

                        </button>

                    </div>
                </div>
                <button type="submit" class="btn btn-login text-white rounded-3">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Masuk
                </button>
                <div class="text-end mt-2">

                    <a href="{{ route('forgot-password') }}"
                    class="text-decoration-none">

                        Lupa Password?

                    </a>

                </div>
            </form>
        </div>
    </div>
    <script src="{{ asset('assets/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js') }}"></script>
    <script>

        const togglePassword =
        document.getElementById('togglePassword');

        const password =
        document.getElementById('password');

        togglePassword.addEventListener('click', function(){

            const type =
            password.getAttribute('type') === 'password'
            ? 'text'
            : 'password';

            password.setAttribute('type', type);

            this.innerHTML =
            type === 'password'
            ? '<i class="bi bi-eye"></i>'
            : '<i class="bi bi-eye-slash"></i>';

        });

    </script>
</body>
</html>