<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Aircraft Weight & Balance') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="d-flex flex-column vh-100 overflow-hidden">
    <div class="container-fluid flex-grow-1">
        <div class="row h-100">
            <nav id="sidebar" class="col-md-3 col-lg-2 bg-dark-subtle vh-100 overflow-auto">
                <livewire:sidebar />
            </nav>

            <main class="col-md-9 col-lg-10 px-4 overflow-auto my-2 mx-0" style="height: 100vh;">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                @yield('content')
            </main>
        </div>
    </div>

    <footer class="footer bg-dark-subtle text-center py-2">
        <span class="text-muted">© {{ date('Y') }} Aircraft Weight & Balance. All rights reserved.</span>
    </footer>
</body>

</html>
