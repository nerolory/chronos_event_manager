<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Chronos Core')</title>

    @vite(['resources/css/app.scss', 'resources/js/app.js'])
</head>
<body>
    <div id="app">
        <header class="main-header">
            <div class="container main-header__inner">
                <a href="/" class="logo">
                    <img src="{{ asset('images/icons/logo.svg') }}" alt="Logo" class="logo__icon">
                    <span class="logo__text">Chronos<span>2026</span></span>
                </a>

                <nav class="main-nav">
                    @auth
                        <div class="main-nav__user">
                            <span class="main-nav__username">{{ auth()->user()->name }}</span>
                            <form action="{{ route('logout') }}" method="POST" class="inline-form">
                                @csrf
                                <button type="submit" class="btn-logout-icon" title="Выйти">
                                    <img src="{{ asset('images/icons/logout.svg') }}" alt="Exit">
                                </button>
                            </form>
                        </div>
                    @endauth
                </nav>
            </div>
        </header>

        <main class="page-content">
            @yield('content')
        </main>

        <footer class="main-footer">
            <div class="container">
                <p>&copy; {{ date('Y') }} Chronos Event System</p>
            </div>
        </footer>
    </div>
</body>
</html>