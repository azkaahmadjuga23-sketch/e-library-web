@extends('layouts.app')
@section('content')

<div class="px-8 py-16">

    {{-- Header --}}
    <div class="mb-16">
        <p class="section-label mb-3">Koleksi Pribadi</p>
        <h1 class="font-serif text-5xl font-normal">Library Saya</h1>
    </div>

    {{-- SEDANG DIBACA --}}
    <section class="mb-16">
        <div class="flex items-center gap-6 mb-8">
            <p class="section-label">Sedang Dibaca</p>
            <div class="flex-1 border-t border-border"></div>
            <span class="section-label">{{ $reading->count() }}</span>
        </div>

        @forelse($reading as $book)
        <div class="flex gap-6 py-6 border-b border-border group">
            <div class="w-12 h-16 bg-ink-mute flex-shrink-0 overflow-hidden">
                @if($book->cover_url)
                    <img src="{{ $book->cover_url }}" class="w-full h-full object-cover">
                @endif
            </div>
            <div class="flex-1">
                <a href="{{ route('books.show', str_replace('/', '__', ltrim($book->ol_key, '/'))) }}"
                   class="font-medium text-text hover:text-accent transition-colors">
                    {{ $book->title }}
                </a>
                <p class="text-muted text-xs mt-1">{{ $book->author }}</p>
                @if($book->pivot->started_reading_at)
                    <p class="text-xs tracking-widest uppercase text-muted mt-2">
                        Mulai {{ \Carbon\Carbon::parse($book->pivot->started_reading_at)->format('d M Y') }}
                    </p>
                @endif
            </div>
            <div class="flex items-center gap-4">
                @if($book->is_readable)
                    <a href="{{ route('books.read', $book->id) }}"
                       class="section-label hover:text-text transition-colors">
                        Lanjut →
                    </a>
                @endif
            </div>
        </div>
        @empty
            <p class="text-muted text-sm py-6">Belum ada buku yang sedang dibaca.</p>
        @endforelse
    </section>

    {{-- WISHLIST --}}
    <section class="mb-16">
        <div class="flex items-center gap-6 mb-8">
            <p class="section-label">Wishlist</p>
            <div class="flex-1 border-t border-border"></div>
            <span class="section-label">{{ $wishlist->count() }}</span>
        </div>

        @forelse($wishlist as $book)
        <div class="flex gap-6 py-6 border-b border-border group">
            <div class="w-12 h-16 bg-ink-mute flex-shrink-0 overflow-hidden">
                @if($book->cover_url)
                    <img src="{{ $book->cover_url }}" class="w-full h-full object-cover">
                @endif
            </div>
            <div class="flex-1">
                <a href="{{ route('books.show', str_replace('/', '__', ltrim($book->ol_key, '/'))) }}"
                   class="font-medium text-text hover:text-accent transition-colors">
                    {{ $book->title }}
                </a>
                <p class="text-muted text-xs mt-1">{{ $book->author }}</p>
            </div>
        </div>
        @empty
            <p class="text-muted text-sm py-6">Wishlist kosong.</p>
        @endforelse
    </section>

    {{-- SELESAI --}}
    <section>
        <div class="flex items-center gap-6 mb-8">
            <p class="section-label">Selesai Dibaca</p>
            <div class="flex-1 border-t border-border"></div>
            <span class="section-label">{{ $finished->count() }}</span>
        </div>

        @forelse($finished as $book)
        <div class="flex gap-6 py-6 border-b border-border group">
            <div class="w-12 h-16 bg-ink-mute flex-shrink-0 overflow-hidden">
                @if($book->cover_url)
                    <img src="{{ $book->cover_url }}" class="w-full h-full object-cover">
                @endif
            </div>
            <div class="flex-1">
                <a href="{{ route('books.show', str_replace('/', '__', ltrim($book->ol_key, '/'))) }}"
                   class="font-medium text-text hover:text-accent transition-colors">
                    {{ $book->title }}
                </a>
                <p class="text-muted text-xs mt-1">{{ $book->author }}</p>
                @if($book->pivot->finished_at)
                    <p class="text-xs tracking-widest uppercase text-muted mt-2">
                        Selesai {{ \Carbon\Carbon::parse($book->pivot->finished_at)->format('d M Y') }}
                    </p>
                @endif
            </div>
            <div class="flex items-center">
                @if($book->is_readable)
                    <a href="{{ route('books.read', $book->id) }}"
                       class="section-label hover:text-text transition-colors">
                        Baca Ulang →
                    </a>
                @endif
            </div>
        </div>
        @empty
            <p class="text-muted text-sm py-6">Belum ada buku yang selesai dibaca.</p>
        @endforelse
    </section>

</div>

@endsection
