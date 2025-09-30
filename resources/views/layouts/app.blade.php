<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'My Movie App')</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    @stack('styles')
</head>
<body class="bg-light d-flex flex-column min-vh-100">

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
        <div class="container">
             <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('search.list') }}">View Searches</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('search.form.show') }}">New Search</a>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="container flex-grow-1">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-dark text-white text-center py-3 mt-auto">
        <p class="mb-0">&copy; {{ date('Y') }} Movie Dashboard. All rights reserved.</p>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    @stack('scripts')
</body>
</html>
