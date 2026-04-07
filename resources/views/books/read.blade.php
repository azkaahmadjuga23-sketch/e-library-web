@extends('layouts.app')
@section('content')

{{-- HEADER --}}
<div class="px-8 py-5 border-b border-border flex items-center justify-between">
    <div>
        <p class="section-label mb-1">Sedang Dibaca</p>
        <h1 class="font-serif text-xl font-normal text-text">{{ $book->title }}</h1>
    </div>
    <a href="{{ route('books.show', str_replace('/', '__', ltrim($book->ol_key, '/'))) }}"
       class="section-label hover:text-text transition-colors">
        ← Kembali ke Detail
    </a>
</div>

{{-- READER --}}
<div class="w-full bg-ink-mute" style="height: calc(100vh - 140px);">
    <iframe
        src="{{ $book->embed_url }}"
        width="100%"
        height="100%"
        frameborder="0"
        allowfullscreen
        class="w-full h-full">
        <p class="text-muted p-8">
            Browser kamu tidak mendukung iframe.
            <a href="{{ $book->embed_url }}" target="_blank" class="text-text underline">Buka di tab baru</a>
        </p>
    </iframe>
</div>

@endsection
