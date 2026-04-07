@extends('layouts.app')
@section('content')

{{-- HERO --}}
<section class="px-8 py-32 border-b border-border">
    <div class="max-w-4xl">
        <p class="section-label mb-6">Digital Library</p>
        <h1 class="font-serif text-6xl md:text-8xl font-normal leading-none text-text mb-8">
            Baca.<br>
            <em class="text-accent">Simpan.</em><br>
            Temukan.
        </h1>
        <p class="text-muted text-sm tracking-wide mb-12 max-w-md">
            Ribuan judul buku tersedia. Gratis, tanpa batas.
        </p>

        <form method="GET" action="{{ route('books.search') }}" class="flex gap-0 max-w-xl">
            <input
                type="text"
                name="q"
                placeholder="Cari judul, pengarang, topik..."
                autofocus
                class="flex-1 bg-ink-mute border border-border text-text placeholder-muted px-6 py-4 text-sm focus:outline-none focus:border-text transition-colors"
            >
            <button type="submit" class="btn-primary px-8 py-4 text-xs">
                Cari
            </button>
        </form>
    </div>
</section>

{{-- LIBRARY SAYA --}}
@auth
    @if(!empty($myLibrary))
    <section class="px-8 py-16 border-b border-border">
        <div class="flex items-end justify-between mb-10">
            <div>
                <p class="section-label mb-3">Aktivitas Terakhir</p>
                <h2 class="font-serif text-3xl font-normal">Library Saya</h2>
            </div>
            <a href="{{ route('books.library') }}" class="section-label hover:text-text transition-colors">
                Lihat Semua →
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-px bg-border">
            @foreach($myLibrary as $book)
            <div class="bg-ink-soft p-6 flex flex-col gap-4">
                {{-- Cover --}}
                <div class="aspect-[3/4] bg-ink-mute overflow-hidden">
                    @if($book['cover_url'])
                        <img src="{{ $book['cover_url'] }}" alt="{{ $book['title'] }}"
                             class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full flex items-center justify-center">
                            <span class="section-label">No Cover</span>
                        </div>
                    @endif
                </div>

                {{-- Info --}}
                <div class="flex-1">
                    <p class="text-text font-medium text-sm leading-snug mb-1">{{ $book['title'] }}</p>
                    <p class="text-muted text-xs mb-3">{{ $book['author'] ?? '-' }}</p>

                    {{-- Status badge --}}
                    @php
                        $statusColor = match($book['status']) {
                            'reading'  => 'text-accent',
                            'finished' => 'text-green-400',
                            default    => 'text-muted',
                        };
                        $statusLabel = match($book['status']) {
                            'reading'  => 'Sedang Dibaca',
                            'wishlist' => 'Wishlist',
                            'finished' => 'Selesai',
                            default    => ucfirst($book['status']),
                        };
                    @endphp
                    <p class="text-xs tracking-widest uppercase {{ $statusColor }}">{{ $statusLabel }}</p>
                </div>

                {{-- Actions --}}
                <div class="flex gap-3 text-xs tracking-widest uppercase">
                    <a href="{{ route('books.show', str_replace('/', '__', ltrim($book['ol_key'], '/'))) }}"
                       class="text-muted hover:text-text transition-colors">
                        Detail
                    </a>
                    @if($book['is_readable'])
                        <span class="text-border">|</span>
                        <a href="{{ route('books.read', $book['id']) }}"
                           class="text-muted hover:text-text transition-colors">
                            {{ $book['status'] === 'finished' ? 'Baca Ulang' : 'Lanjut Baca' }}
                        </a>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </section>
    @endif
@endauth

{{-- REKOMENDASI --}}
<section class="px-8 py-16 border-b border-border">
    <div class="mb-10">
        <p class="section-label mb-3">Pilihan Editor</p>
        <h2 class="font-serif text-3xl font-normal">Rekomendasi</h2>
    </div>
    @include('books._book_grid', ['books' => $recommendations])
</section>

{{-- TRENDING --}}
<section class="px-8 py-16 border-b border-border">
    <div class="mb-10">
        <p class="section-label mb-3">Sedang Populer</p>
        <h2 class="font-serif text-3xl font-normal">Trending</h2>
    </div>
    @include('books._book_grid', ['books' => $trending])
</section>

{{-- MOST READ --}}
<section class="px-8 py-16">
    <div class="mb-10">
        <p class="section-label mb-3">Paling Banyak Dibaca</p>
        <h2 class="font-serif text-3xl font-normal">Most Read</h2>
    </div>
    @include('books._book_grid', ['books' => $mostRead])
</section>

@endsection
