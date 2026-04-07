@extends('layouts.app')
@section('content')

<div class="min-h-screen flex items-center justify-center px-8">
    <div class="w-full max-w-md">

        {{-- Header --}}
        <div class="mb-12">
            <p class="section-label mb-4">Selamat Datang</p>
            <h1 class="font-serif text-5xl font-normal">Masuk</h1>
        </div>

        {{-- Error --}}
        @if($errors->any())
            <div class="border border-red-400 px-6 py-4 mb-8">
                <p class="text-red-400 text-xs tracking-widest uppercase">{{ $errors->first() }}</p>
            </div>
        @endif

        {{-- Form --}}
        <form method="POST" action="{{ route('login') }}" class="flex flex-col gap-6">
            @csrf

            <div class="flex flex-col gap-2">
                <label class="section-label">Email</label>
                <input
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    required
                    class="bg-ink-mute border border-border text-text px-5 py-4 text-sm focus:outline-none focus:border-text transition-colors"
                >
            </div>

            <div class="flex flex-col gap-2">
                <label class="section-label">Password</label>
                <input
                    type="password"
                    name="password"
                    required
                    class="bg-ink-mute border border-border text-text px-5 py-4 text-sm focus:outline-none focus:border-text transition-colors"
                >
            </div>

            <button type="submit" class="btn-primary mt-4">
                Masuk
            </button>
        </form>

        <div class="border-t border-border mt-10 pt-8">
            <p class="text-muted text-xs">
                Belum punya akun?
                <a href="{{ route('register') }}" class="text-text hover:text-accent transition-colors">
                    Register
                </a>
            </p>
        </div>

    </div>
</div>

@endsection
