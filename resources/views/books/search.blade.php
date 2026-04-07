@extends('layouts.app')
@section('content')

<div class="px-8 py-16">

    {{-- Search bar --}}
    <div class="mb-12">
        <p class="section-label mb-6">Pencarian</p>
        <form method="GET" action="{{ route('books.search') }}" class="flex gap-0 max-w-2xl">
            <input
                type="text"
                name="q"
                value="{{ $query }}"
                placeholder="Cari judul, pengarang, topik..."
                class="flex-1 bg-ink-mute border border-border text-text placeholder-muted px-6 py-4 text-sm focus:outline-none focus:border-text transition-colors"
            >
            <button type="submit" class="btn-primary px-8 py-4 text-xs">Cari</button>
        </form>
    </div>

    {{-- Back --}}
    <a href="{{ route('books.index') }}" class="section-label hover:text-text transition-colors inline-block mb-12">
        ← Kembali ke Home
    </a>

    {{-- Results --}}
    @if($query && empty($results))
        <div class="border border-border px-8 py-12 text-center">
            <p class="section-label mb-3">Tidak ditemukan</p>
            <p class="text-muted text-sm">Tidak ada hasil untuk "<span class="text-text">{{ $query }}</span>".</p>
        </div>
    @elseif(!empty($results))
        <div class="mb-8">
            <p class="section-label">
                Hasil untuk: <span class="text-text">{{ $query }}</span>
            </p>
        </div>
        @include('books._book_grid', ['books' => $results])
    @endif

</div>

@endsection
