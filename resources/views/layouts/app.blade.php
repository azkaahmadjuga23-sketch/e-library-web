<!DOCTYPE html>
<html lang="id" class="bg-ink">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Library</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-ink text-text font-sans min-h-screen flex flex-col">

    {{-- NAVBAR --}}
    <nav class="border-b border-border px-8 py-5 flex items-center justify-between sticky top-0 bg-ink z-50">
        <a href="{{ route('books.index') }}" class="font-serif text-xl tracking-widest uppercase text-text hover:text-accent transition-colors">
            E-Library
        </a>

        <div class="flex items-center gap-8 text-xs tracking-widest uppercase">
            @auth
                <a href="{{ route('books.library') }}" class="text-muted hover:text-text transition-colors">
                    Library
                </a>
                <span class="text-muted">{{ Auth::user()->name }}</span>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="text-muted hover:text-text transition-colors">
                        Logout
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}" class="text-muted hover:text-text transition-colors">
                    Login
                </a>
                <a href="{{ route('register') }}" class="btn-primary text-xs py-2 px-4">
                    Register
                </a>
            @endauth
        </div>
    </nav>

    {{-- FLASH MESSAGES --}}
    @if(session('success') || session('error') || session('info'))
    <div class="px-8 py-4 border-b border-border">
        @if(session('success'))
            <p class="text-xs tracking-widest uppercase text-accent">✓ {{ session('success') }}</p>
        @endif
        @if(session('error'))
            <p class="text-xs tracking-widest uppercase text-red-400">✗ {{ session('error') }}</p>
        @endif
        @if(session('info'))
            <p class="text-xs tracking-widest uppercase text-muted">— {{ session('info') }}</p>
        @endif
    </div>
    @endif

    {{-- MAIN --}}
    <main class="flex-1">
        @yield('content')
    </main>

    {{-- FOOTER --}}
    <footer class="border-t border-border px-8 py-6 mt-20">
        <div class="flex items-center justify-between">
            <span class="font-serif text-sm text-muted">E-Library</span>
            <span class="section-label">{{ date('Y') }}</span>
        </div>
    </footer>

</body>
</html>
