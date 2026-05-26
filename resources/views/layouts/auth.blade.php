<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') - {{ config('app.name') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <style>
        * { font-family: 'Poppins', sans-serif; }
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #7367f0 0%, #5e50ee 50%, #0dcaf0 100%);
            padding: 1rem;
        }
        .auth-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.15);
            padding: 2.5rem;
            width: 100%;
            max-width: 440px;
        }
        .auth-logo {
            text-align: center;
            margin-bottom: 1.5rem;
        }
        .auth-logo img { max-height: 60px; }
        .auth-logo h3 {
            font-weight: 700;
            color: #7367f0;
            margin-top: 0.5rem;
        }
        .auth-logo p {
            color: #6c757d;
            font-size: 0.9rem;
        }
        .form-control {
            border-radius: 10px;
            padding: 0.75rem 1rem;
            border: 1.5px solid #e0e0e0;
        }
        .form-control:focus {
            border-color: #7367f0;
            box-shadow: 0 0 0 3px rgba(115,103,240,0.15);
        }
        .btn-primary {
            background: #7367f0;
            border: none;
            border-radius: 10px;
            padding: 0.75rem;
            font-weight: 600;
        }
        .btn-primary:hover { background: #5e50ee; }
        .auth-footer { text-align: center; margin-top: 1.5rem; }
        .auth-footer a { color: #7367f0; text-decoration: none; font-weight: 500; }
        .auth-footer a:hover { text-decoration: underline; }
        @media (max-width: 480px) {
            .auth-card { padding: 1.5rem; }
        }
    </style>
    @stack('styles')
</head>
<body>
    <div class="auth-card">
        <div class="auth-logo">
            <i class="fas fa-glass-cheers fa-3x" style="color:#7367f0;"></i>
            <h3>{{ config('app.name', 'MyBar') }}</h3>
            <p>Point of Sale System</p>
        </div>
        @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @stack('scripts')
</body>
</html>
