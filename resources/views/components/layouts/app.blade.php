<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Aircraft Weight & Balance') }}</title>
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])

</head>

<body class="d-flex flex-column vh-100 overflow-hidden">
    <div class="container-fluid flex-grow-1">
        <div class="row h-100">
            <!-- Sidebar (collapsible for small screens) -->
            <nav id="sidebar" class="col-md-3 col-lg-2 bg-dark-subtle vh-100 d-none d-md-block">
                <livewire:components.sidebar />
            </nav>

            <div class="offcanvas offcanvas-start" tabindex="-1" id="mobileSidebar" aria-labelledby="mobileSidebarLabel">
                <div class="offcanvas-header">
                    <h5 class="offcanvas-title" id="mobileSidebarLabel"> <i class="bi bi-list"></i> Menu</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                </div>
                <div class="offcanvas-body">
                    <livewire:components.sidebar />
                </div>
            </div>

            <main class="col-md-9 col-lg-10 px-3 px-md-4 overflow-auto my-2 mx-0" style="height: 100vh;">
                <!-- Top Navbar (Show toggle for mobile) -->
                <div class="d-md-none mb-3">
                    <button class="btn btn-secondary" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebar"
                        aria-controls="mobileSidebar">
                        <i class="bi bi-list"></i> Menu
                    </button>
                </div>

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
                {{ $slot }}
            </main>
        </div>
    </div>
</body>

</html>
