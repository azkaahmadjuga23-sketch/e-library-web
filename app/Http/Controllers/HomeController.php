<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Services\OpenLibraryService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function __construct(private OpenLibraryService $olService) {}

    public function index()
    {
        $this->seedFallbackBooks();
        // dd(Book::all(['id','title','ol_key','ia_identifier']));

        $recommendations = $this->getRecommendations();
        $trending        = $this->getTrending();
        $mostRead        = $this->getMostRead();
        $myLibrary       = $this->getMyLibrary();

        return view('books.index', compact(
            'recommendations',
            'trending',
            'mostRead',
            'myLibrary'
        ));
    }

    public function search(\Illuminate\Http\Request $request)
    {
        $query   = $request->get('q');
        $results = [];

        if ($query) {
            // Search TIDAK di-cache karena query-nya dinamis
            $results = $this->olService->search($query, 20);
        }

        return view('books.search', compact('results', 'query'));
    }

    private function seedFallbackBooks(): void
    {
        $allFallbacks = array_merge(
            $this->getFallbackBooks('classic'),
            $this->getFallbackBooks('trending'),
            $this->getFallbackBooks('history')
        );

        foreach ($allFallbacks as $b) {
            Book::updateOrCreate(
                ['ol_key' => $b['ol_key']],
                [
                    'title'         => $b['title'],
                    'author'        => $b['author'],
                    'cover_url'     => $b['cover_url'],
                    'ia_identifier' => $b['ia_identifier'],
                    'pdf_url'       => $b['pdf_url'],
                    'description'   => $b['description'] ?? null,
                    'subject'       => $b['subject'] ?? null,
                ]
            );
        }
    }

    // ─── Private ──────────────────────────────────────────────────────────────

    private function getRecommendations(): array
    {
        return $this->getFallbackBooks('classic');
    }

    private function getTrending(): array
    {
        return $this->getFallbackBooks('trending');
    }

    private function getMostRead(): array
    {
        return Cache::remember('home.most_read', now()->addMinutes(30), function () {
            $bookIds = DB::table('user_books')
                ->select('book_id', DB::raw('count(*) as read_count'))
                ->whereIn('status', ['reading', 'finished'])
                ->groupBy('book_id')
                ->orderByDesc('read_count')
                ->limit(4)
                ->pluck('book_id');

            if ($bookIds->count() >= 4) {
                $books = Book::whereIn('id', $bookIds)->get();
                return $books->map(fn($b) => $this->bookModelToArray($b))->toArray();
            }

            $results = $this->olService->search('subject:history biography', 4);
            return $results ?: $this->getFallbackBooks('history');
        });
    }

    private function getMyLibrary(): array
    {
        if (!Auth::check()) {
            return [];
        }

        // Library milik user — jangan di-cache (personal data, harus realtime)
        return Auth::user()
            ->books()
            ->withPivot(['status', 'started_reading_at', 'finished_at'])
            ->orderByPivot('updated_at', 'desc')
            ->limit(4)
            ->get()
            ->map(fn($book) => [
                'id'                 => $book->id,
                'ol_key'             => $book->ol_key,
                'title'              => $book->title,
                'author'             => $book->author,
                'cover_url'          => $book->cover_url,
                'is_readable'        => $book->is_readable,
                'status'             => $book->pivot->status,
                'started_reading_at' => $book->pivot->started_reading_at,
                'finished_at'        => $book->pivot->finished_at,
            ])
            ->toArray();
    }

    /**
     * Fallback hardcoded jika API benar-benar tidak bisa diakses
     * Menggunakan buku yang sudah pasti ada di Open Library
     */
    private function getFallbackBooks(string $type): array
    {
        $fallbacks = [
            'classic' => [
                ['ol_key' => '/works/OL468431W',  'url_key' => 'works__OL468431W',  'title' => 'The Great Gatsby',          'author' => 'F. Scott Fitzgerald', 'cover_url' => 'https://covers.openlibrary.org/b/id/10590366-M.jpg',  'is_readable' => true, 'ia_identifier' => 'greatgatsby00fitz',       'pdf_url' => 'https://archive.org/download/greatgatsby00fitz/greatgatsby00fitz.pdf',       'description' => 'A story of the fabulously wealthy Jay Gatsby and his love for the beautiful Daisy Buchanan, set in the Jazz Age on Long Island.',          'subject' => 'Fiction, Classics, American Literature'],
                ['ol_key' => '/works/OL66554W',   'url_key' => 'works__OL66554W',   'title' => 'Pride and Prejudice',       'author' => 'Jane Austen',         'cover_url' => 'https://archive.org/services/img/prideprejudice00aust',  'is_readable' => true, 'ia_identifier' => 'prideprejudice00aust',    'pdf_url' => 'https://archive.org/download/prideprejudice00aust/prideprejudice00aust.pdf',   'description' => 'The story follows Elizabeth Bennet as she deals with issues of manners, upbringing, morality, education, and marriage in early 19th-century England.', 'subject' => 'Fiction, Classics, Romance'],
                ['ol_key' => '/works/OL102749W',  'url_key' => 'works__OL102749W',  'title' => 'Moby Dick',                 'author' => 'Herman Melville',     'cover_url' => 'https://archive.org/services/img/mobydickorwhale00melv', 'is_readable' => true, 'ia_identifier' => 'mobydickorwhale00melv',   'pdf_url' => 'https://archive.org/download/mobydickorwhale00melv/mobydickorwhale00melv.pdf', 'description' => 'The saga of Captain Ahab and his obsessive quest to slay the white whale Moby Dick.',                                                      'subject' => 'Fiction, Classics, Adventure'],
                ['ol_key' => '/works/OL266178W',  'url_key' => 'works__OL266178W',  'title' => 'The Diary of a Young Girl', 'author' => 'Anne Frank',          'cover_url' => 'https://archive.org/services/img/diaryofyounggirl00fran', 'is_readable' => true, 'ia_identifier' => 'diaryofyounggirl00fran',  'pdf_url' => 'https://archive.org/download/diaryofyounggirl00fran/diaryofyounggirl00fran.pdf', 'description' => 'The diary of Anne Frank, a Jewish girl who hid with her family during the Nazi occupation of the Netherlands.',                             'subject' => 'Biography, History, World War II'],
            ],
            'trending' => [
                ['ol_key' => '/works/OL1892617W', 'url_key' => 'works__OL1892617W', 'title' => 'A Brief History of Time',  'author' => 'Stephen Hawking',     'cover_url' => 'https://archive.org/services/img/briefhistoryoftim00hawk', 'is_readable' => true, 'ia_identifier' => 'briefhistoryoftim00hawk', 'pdf_url' => 'https://archive.org/download/briefhistoryoftim00hawk/briefhistoryoftim00hawk.pdf', 'description' => 'A landmark volume in science writing that explores the nature of space and time, the role of God in creation, and the history of scientific thought.', 'subject' => 'Science, Physics, Cosmology'],
                ['ol_key' => '/works/OL468431W',  'url_key' => 'works__OL468431W',  'title' => 'The Great Gatsby',         'author' => 'F. Scott Fitzgerald', 'cover_url' => 'https://covers.openlibrary.org/b/id/10590366-M.jpg',  'is_readable' => true, 'ia_identifier' => 'greatgatsby00fitz',       'pdf_url' => 'https://archive.org/download/greatgatsby00fitz/greatgatsby00fitz.pdf',       'description' => 'A story of the fabulously wealthy Jay Gatsby and his love for the beautiful Daisy Buchanan, set in the Jazz Age on Long Island.',          'subject' => 'Fiction, Classics, American Literature'],
                ['ol_key' => '/works/OL66554W',   'url_key' => 'works__OL66554W',   'title' => 'Pride and Prejudice',      'author' => 'Jane Austen',         'cover_url' => 'https://archive.org/services/img/prideprejudice00aust',  'is_readable' => true, 'ia_identifier' => 'prideprejudice00aust',    'pdf_url' => 'https://archive.org/download/prideprejudice00aust/prideprejudice00aust.pdf',   'description' => 'The story follows Elizabeth Bennet as she deals with issues of manners, upbringing, morality, education, and marriage in early 19th-century England.', 'subject' => 'Fiction, Classics, Romance'],
                ['ol_key' => '/works/OL244537W',  'url_key' => 'works__OL244537W',  'title' => 'The Art of War',           'author' => 'Sun Tzu',             'cover_url' => 'https://covers.openlibrary.org/b/id/4849549-M.jpg',   'is_readable' => true, 'ia_identifier' => 'artofwar00suntu',         'pdf_url' => 'https://archive.org/download/artofwar00suntu/artofwar00suntu.pdf',           'description' => 'An ancient Chinese military treatise dating from the 5th century BC, attributed to Sun Tzu.',                                               'subject' => 'Military, Strategy, Philosophy'],
            ],
            'history' => [
                ['ol_key' => '/works/OL244537W',  'url_key' => 'works__OL244537W',  'title' => 'The Art of War',           'author' => 'Sun Tzu',             'cover_url' => 'https://covers.openlibrary.org/b/id/4849549-M.jpg',   'is_readable' => true, 'ia_identifier' => 'artofwar00suntu',         'pdf_url' => 'https://archive.org/download/artofwar00suntu/artofwar00suntu.pdf',           'description' => 'An ancient Chinese military treatise dating from the 5th century BC, attributed to Sun Tzu.',                                               'subject' => 'Military, Strategy, Philosophy'],
                ['ol_key' => '/works/OL266178W',  'url_key' => 'works__OL266178W',  'title' => 'The Diary of a Young Girl', 'author' => 'Anne Frank',         'cover_url' => 'https://archive.org/services/img/diaryofyounggirl00fran', 'is_readable' => true, 'ia_identifier' => 'diaryofyounggirl00fran',  'pdf_url' => 'https://archive.org/download/diaryofyounggirl00fran/diaryofyounggirl00fran.pdf', 'description' => 'The diary of Anne Frank, a Jewish girl who hid with her family during the Nazi occupation of the Netherlands.',                             'subject' => 'Biography, History, World War II'],
                ['ol_key' => '/works/OL102749W',  'url_key' => 'works__OL102749W',  'title' => 'Moby Dick',                'author' => 'Herman Melville',     'cover_url' => 'https://archive.org/services/img/mobydickorwhale00melv', 'is_readable' => true, 'ia_identifier' => 'mobydickorwhale00melv',   'pdf_url' => 'https://archive.org/download/mobydickorwhale00melv/mobydickorwhale00melv.pdf', 'description' => 'The saga of Captain Ahab and his obsessive quest to slay the white whale Moby Dick.',                                                      'subject' => 'Fiction, Classics, Adventure'],
                ['ol_key' => '/works/OL468431W',  'url_key' => 'works__OL468431W',  'title' => 'The Great Gatsby',         'author' => 'F. Scott Fitzgerald', 'cover_url' => 'https://covers.openlibrary.org/b/id/10590366-M.jpg',  'is_readable' => true, 'ia_identifier' => 'greatgatsby00fitz',       'pdf_url' => 'https://archive.org/download/greatgatsby00fitz/greatgatsby00fitz.pdf',       'description' => 'A story of the fabulously wealthy Jay Gatsby and his love for the beautiful Daisy Buchanan, set in the Jazz Age on Long Island.',          'subject' => 'Fiction, Classics, American Literature'],
            ],
        ];

        return $fallbacks[$type] ?? [];
    }

    private function bookModelToArray(Book $book): array
    {
        return [
            'ol_key'      => $book->ol_key,
            'url_key'     => str_replace('/', '__', ltrim($book->ol_key ?? '', '/')),
            'title'       => $book->title,
            'author'      => $book->author,
            'cover_url'   => $book->cover_url,
            'is_readable' => $book->is_readable,
        ];
    }
}
