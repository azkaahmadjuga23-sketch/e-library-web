<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Services\OpenLibraryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookController extends Controller
{
    public function __construct(private OpenLibraryService $olService) {}

    /**
     * Landing page + search
     */
    public function index(Request $request)
    {
        $results = [];
        $query   = $request->get('q');

        if ($query) {
            $results = $this->olService->search($query);
        }

        return view('books.index', compact('results', 'query'));
    }

    /**
     * Detail buku — fetch dari API, simpan ke DB jika belum ada
     */
    public function show(Request $request, string $olKey)
    {
        $olKeyDecoded = '/' . str_replace('__', '/', $olKey);

        $book = Book::where('ol_key', $olKeyDecoded)->first();

        if (!$book) {
            // Buku belum ada di DB — fetch dari API sekali
            $detail = $this->olService->getDetail($olKeyDecoded);

            if (!$detail) {
                abort(503, 'Buku tidak ditemukan dan API tidak tersedia.');
            }

            $book = Book::create([
                'ol_key'        => $olKeyDecoded,
                'title'         => $detail['title'],
                'author'        => $detail['author'],
                'description'   => $detail['description'] ?? null,
                'cover_url'     => $detail['cover_url'] ?? null,
                'ia_identifier' => $detail['ia_identifier'] ?? null,
                'pdf_url'       => $detail['pdf_url'] ?? null,
                'subject'       => $detail['subject'] ?? null,
            ]);
        }

        $detail = [
            'ol_key'        => $book->ol_key,
            'title'         => $book->title,
            'author'        => $book->author,
            'description'   => $book->description,
            'cover_url'     => $book->cover_url,
            'ia_identifier' => $book->ia_identifier,
            'pdf_url'       => $book->pdf_url,
            'subject'       => $book->subject,
            'is_readable'   => $book->is_readable,
        ];

        $userBook = null;
        if (Auth::check()) {
            $userBook = Auth::user()->books()
                ->where('book_id', $book->id)
                ->first();
        }

        return view('books.show', compact('book', 'userBook', 'detail'));
    }

    /**
     * Buka PDF reader — otomatis set status ke "reading"
     */
    public function read(Book $book)
    {
        if (!$book->is_readable) {
            return back()->with('error', 'Buku ini tidak tersedia untuk dibaca online.');
        }

        $this->updateOrCreateUserBook($book, 'reading', [
            'started_reading_at' => now(),
        ]);

        return view('books.read', compact('book'));
    }

    /**
     * Download PDF
     */
    public function download(Book $book)
    {
        if (!$book->pdf_url) {
            return back()->with('error', 'PDF tidak tersedia untuk buku ini.');
        }

        $this->updateOrCreateUserBook($book, 'reading', [
            'started_reading_at' => now(),
        ]);

        return redirect($book->pdf_url);
    }

    /**
     * Toggle wishlist
     */
    public function toggleWishlist(Book $book)
    {
        $user     = Auth::user();
        $existing = $user->books()->where('book_id', $book->id)->first();

        if ($existing) {
            if ($existing->pivot->status === 'wishlist') {
                $user->books()->detach($book->id);
                return back()->with('success', 'Dihapus dari Wishlist.');
            }
            return back()->with('info', "Buku sudah berstatus '{$existing->pivot->status}'.");
        }

        $user->books()->attach($book->id, [
            'status'     => 'wishlist',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Ditambahkan ke Wishlist.');
    }

    /**
     * Tandai selesai
     */
    public function markFinished(Book $book)
    {
        $this->updateOrCreateUserBook($book, 'finished', [
            'finished_at' => now(),
        ]);

        return back()->with('success', 'Buku ditandai selesai dibaca.');
    }

    /**
     * Library milik user (reading now + wishlist + finished)
     */
    public function library()
    {
        $user     = Auth::user();
        $reading  = $user->readingNow()->get();
        $wishlist = $user->wishlist()->get();
        $finished = $user->finished()->get();

        return view('books.library', compact('reading', 'wishlist', 'finished'));
    }

    // ─── Private Helper ───────────────────────────────────────────────────────

    private function updateOrCreateUserBook(Book $book, string $status, array $extra = []): void
    {
        $user     = Auth::user();
        $existing = $user->books()->where('book_id', $book->id)->first();

        if ($existing) {
            $priority = ['wishlist' => 1, 'reading' => 2, 'finished' => 3];
            if (($priority[$status] ?? 0) <= ($priority[$existing->pivot->status] ?? 0)) {
                return;
            }
            $user->books()->updateExistingPivot($book->id, array_merge(
                ['status' => $status, 'updated_at' => now()],
                $extra
            ));
        } else {
            $user->books()->attach($book->id, array_merge(
                ['status' => $status, 'created_at' => now(), 'updated_at' => now()],
                $extra
            ));
        }
    }
}
