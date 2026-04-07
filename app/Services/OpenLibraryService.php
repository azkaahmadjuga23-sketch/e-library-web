<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\ConnectionException;

class OpenLibraryService
{
    private string $baseUrl   = 'https://openlibrary.org';
    private string $coversUrl = 'https://covers.openlibrary.org';

    public function search(string $query, int $limit = 20): array
    {
        try {
            $response = Http::timeout(15)
                ->retry(2, 2000) // 2x retry, jeda 2 detik
                ->get("{$this->baseUrl}/search.json", [
                    'q'      => $query,
                    'limit'  => $limit,
                    'fields' => 'key,title,author_name,first_publish_year,cover_i,ia,subject,edition_count',
                ]);

            if ($response->failed()) {
                Log::warning('OL search failed', ['query' => $query, 'status' => $response->status()]);
                return [];
            }

            return collect($response->json('docs', []))
                ->map(fn($doc) => $this->normalizeSearchResult($doc))
                ->toArray();
        } catch (ConnectionException $e) {
            Log::error('OL search timeout', ['query' => $query, 'error' => $e->getMessage()]);
            return []; // Kembalikan array kosong, jangan crash
        } catch (\Exception $e) {
            Log::error('OL search error', ['query' => $query, 'error' => $e->getMessage()]);
            return [];
        }
        $response = Http::timeout(15)
            ->retry(2, 2000)
            ->get("{$this->baseUrl}/search.json", [
                'q'          => $query,
                'limit'      => $limit,
                'fields'     => 'key,title,author_name,first_publish_year,cover_i,ia,subject,edition_count',
                'has_fulltext' => 'true', // ← tambahkan ini
            ]);
    }

    public function getDetail(string $olKey): ?array
    {
        try {
            $response = Http::timeout(15)
                ->retry(2, 2000)
                ->get("{$this->baseUrl}{$olKey}.json");

            if ($response->failed()) {
                return null;
            }

            $data         = $response->json();
            $availability = $this->checkAvailability($olKey);

            return [
                'ol_key'        => $olKey,
                'title'         => $data['title'] ?? 'Unknown Title',
                'author'        => $this->extractAuthor($data),
                'description'   => $this->extractDescription($data),
                'cover_url'     => isset($data['covers'][0])
                    ? "{$this->coversUrl}/b/id/{$data['covers'][0]}-L.jpg"
                    : null,
                'ia_identifier' => $availability['ia_identifier'] ?? null,
                'pdf_url'       => $availability['pdf_url'] ?? null,
                'year'          => $data['first_publish_date'] ?? null,
                'subject'       => isset($data['subjects'])
                    ? implode(', ', array_slice($data['subjects'], 0, 3))
                    : null,
                'is_readable'   => !empty($availability['ia_identifier']),
            ];
        } catch (ConnectionException $e) {
            Log::error('OL detail timeout', ['key' => $olKey]);
            return null;
        } catch (\Exception $e) {
            Log::error('OL detail error', ['key' => $olKey, 'error' => $e->getMessage()]);
            return null;
        }
    }

    private function checkAvailability(string $olKey): array
    {
        try {
            $bibkey   = 'olid:' . basename($olKey);
            $response = Http::timeout(8)
                ->retry(1, 1000)
                ->get("{$this->baseUrl}/api/books", [
                    'bibkeys' => $bibkey,
                    'format'  => 'json',
                    'jscmd'   => 'data',
                ]);

            if ($response->failed()) {
                return [];
            }

            $bookData = $response->json()[$bibkey] ?? [];
            $iaId     = $bookData['identifiers']['internet_archive'][0] ?? null;

            return [
                'ia_identifier' => $iaId,
                'pdf_url'       => $iaId
                    ? "https://archive.org/download/{$iaId}/{$iaId}.pdf"
                    : null,
            ];
        } catch (\Exception $e) {
            return []; // Availability check gagal = tidak ada PDF, bukan crash
        }
    }

    private function normalizeSearchResult(array $doc): array
    {
        $iaId = $doc['ia'][0] ?? null;

        return [
            'ol_key'        => $doc['key'] ?? null,
            'url_key'       => str_replace('/', '__', ltrim($doc['key'] ?? '', '/')),
            'title'         => $doc['title'] ?? 'Unknown',
            'author'        => isset($doc['author_name'])
                ? implode(', ', array_slice($doc['author_name'], 0, 2))
                : 'Unknown Author',
            'year'          => $doc['first_publish_year'] ?? null,
            'cover_url'     => isset($doc['cover_i'])
                ? "{$this->coversUrl}/b/id/{$doc['cover_i']}-M.jpg"
                : null,
            'ia_identifier' => $iaId,
            'is_readable'   => !is_null($iaId),
            'edition_count' => $doc['edition_count'] ?? 0,
        ];
    }

    private function extractAuthor(array $data): string
    {
        if (isset($data['authors'][0]['author']['key'])) {
            $authorKey = $data['authors'][0]['author']['key'];

            try {
                $response = Http::timeout(8)->get("{$this->baseUrl}{$authorKey}.json");
                if ($response->successful()) {
                    return $response->json('name') ?? $authorKey;
                }
            } catch (\Exception $e) {
                // fallback ke key jika fetch gagal
            }

            return $authorKey;
        }
        return 'Unknown Author';
    }

    private function extractDescription(array $data): string
    {
        $desc = $data['description'] ?? '';
        if (is_array($desc)) {
            return $desc['value'] ?? '';
        }
        return (string) $desc;
    }
}
