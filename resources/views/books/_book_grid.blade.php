@if(empty($books))
    <p class="section-label">Belum ada buku tersedia.</p>
@else
<div class="grid grid-cols-2 md:grid-cols-4 gap-px bg-border">
    @foreach($books as $book)
        @php
            $b          = is_array($book) ? $book : $book->toArray();
            $urlKey     = $b['url_key'] ?? str_replace('/', '__', ltrim($b['ol_key'] ?? '', '/'));
            $title      = $b['title'] ?? 'Unknown';
            $author     = $b['author'] ?? '-';
            $coverUrl   = $b['cover_url'] ?? null;
            $isReadable = $b['is_readable'] ?? false;
        @endphp
        <div class="card-book bg-ink-soft group">
            {{-- Cover --}}
            <div class="aspect-[3/4] bg-ink-mute overflow-hidden">
                @if($coverUrl)
                    <img src="{{ $coverUrl }}" alt="{{ $title }}"
                         class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                @else
                    <div class="w-full h-full flex items-center justify-center">
                        <span class="section-label">No Cover</span>
                    </div>
                @endif
            </div>

            {{-- Info --}}
            <div class="p-5">
                <p class="text-text text-sm font-medium leading-snug mb-1 line-clamp-2">{{ $title }}</p>
                <p class="text-muted text-xs mb-4">{{ $author }}</p>

                <div class="flex items-center justify-between">
                    @if($isReadable)
                        <span class="text-xs tracking-widest uppercase text-accent">PDF</span>
                    @else
                        <span class="text-xs tracking-widest uppercase text-border">—</span>
                    @endif

                    @if($urlKey)
                        <a href="{{ route('books.show', $urlKey) }}"
                           class="text-xs tracking-widest uppercase text-muted hover:text-text transition-colors">
                            Detail →
                        </a>
                    @endif
                </div>
            </div>
        </div>
    @endforeach
</div>
@endif
