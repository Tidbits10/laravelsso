<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f5f7fb; }
        .topbar { background: #800000; color: #fff; }
        .brand-dot { width: 12px; height: 12px; background: #ffc107; border-radius: 50%; display: inline-block; }
        .stat { border-left: 4px solid #800000; }
    </style>
</head>
<body>
    <nav class="navbar topbar mb-4">
        <div class="container-fluid px-4">
            <span class="navbar-brand mb-0 h1 text-white"><span class="brand-dot me-2"></span>PUP San Pedro SSO</span>
            @auth
                <form method="post" action="{{ route('logout') }}">
                    @csrf
                    <button class="btn btn-sm btn-light">Logout</button>
                </form>
            @endauth
        </div>
    </nav>

    <main class="container-fluid px-4 pb-5">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif
        @yield('content')
    </main>
</body>
</html>
