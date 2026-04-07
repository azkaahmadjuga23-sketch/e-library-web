@extends('layouts.app')
@section('content')

<div class="grid grid-cols-1 md:grid-cols-3 min-h-screen">

    {{-- COVER PANEL --}}
    <div class="border-r border-border bg-ink-soft flex items-start justify-center p-12 pt-20">
        @if($book->cover_url)
            <img src="{{ $book->cover_url }}" alt="{{ $book->title }}"
                 class="w-full max-w-[220px] shadow-2xl">
        @else
            <div class="w-full max-w-[220px] aspect-[3/4] bg-ink-mute flex items-center justify-center">
                <span class="section-label">No Cover</span>
            </div>
        @endif
    </div>

    {{-- DETAIL PANEL --}}
    <div class="md:col-span-2 p-12 pt-20">

        {{-- Breadcrumb --}}
        <a href="{{ route('books.index') }}"
           class="section-label hover:text-text transition-colors mb-10 inline-block">
            ← Kembali
        </a>

        {{-- Title --}}
        <h1 class="font-serif text-5xl font-normal leading-tight text-text mb-4">
            {{ $book->title }}
        </h1>

        <p class="text-muted text-sm mb-2">{{ $book->author ?? '-' }}</p>

        @if($book->subject)
            <p class="section-label mb-10">{{ $book->subject }}</p>
        @endif

        <div class="border-t border-border my-8"></div>

        {{-- Description --}}
        <p class="text-text/70 text-sm leading-relaxed max-w-xl mb-12">
            {{ $book->description ?? 'Tidak ada deskripsi tersedia.' }}
        </p>

        {{-- Status badge --}}
        @auth
            @if($userBook)
                @php
                    $statusColor = match($userBook->pivot->status) {
                        'reading'  => 'text-accent border-accent',
                        'finished' => 'text-green-400 border-green-400',
                        default    => 'text-muted border-border',
                    };
                    $statusLabel = match($userBook->pivot->status) {
                        'reading'  => 'Sedang Dibaca',
                        'wishlist' => 'Di Wishlist',
                        'finished' => 'Selesai Dibaca',
                        default    => ucfirst($userBook->pivot->status),
                    };
                @endphp
                <p class="text-xs tracking-widest uppercase border px-4 py-2 inline-block mb-8 {{ $statusColor }}">
                    {{ $statusLabel }}
                </p>
            @endif
        @endauth

        {{-- Actions --}}
        <div class="flex flex-wrap gap-4">
            @auth
                @if($book->is_readable)
                    <a href="{{ route('books.read', $book->id) }}" class="btn-primary">
                        Baca Online
                    </a>
                @else
                    <span class="btn-ghost opacity-40 cursor-not-allowed">PDF Tidak Tersedia</span>
                @endif

                <form action="{{ route('books.wishlist', $book->id) }}" method="POST">
                    @csrf
                    @if($userBook && $userBook->pivot->status === 'wishlist')
                        <button type="submit" class="btn-ghost">Hapus dari Wishlist</button>
                    @else
                        <button type="submit" class="btn-ghost">+ Wishlist</button>
                    @endif
                </form>

                @if($userBook && $userBook->pivot->status === 'reading')
                    <form action="{{ route('books.finish', $book->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn-ghost">Tandai Selesai</button>
                    </form>
                @endif

            @else
                <a href="{{ route('login') }}" class="btn-primary">Login untuk Membaca</a>
                <a href="{{ route('register') }}" class="btn-ghost">Register</a>
            @endauth
        </div>

    </div>
</div>

@endsection
