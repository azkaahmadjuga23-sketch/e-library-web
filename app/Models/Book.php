<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Book extends Model
{
    protected $fillable = [
        'ol_key',
        'google_books_id',
        'title',
        'author',
        'description',
        'cover_url',
        'ia_identifier',
        'pdf_url',
        'year',
        'subject',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_books')
                    ->withPivot(['status', 'started_reading_at', 'finished_at'])
                    ->withTimestamps();
    }

    /**
     * Generate embed URL untuk Internet Archive reader
     */
    public function getEmbedUrlAttribute(): ?string
    {
        if ($this->ia_identifier) {
            return "https://archive.org/embed/{$this->ia_identifier}";
        }
        if ($this->pdf_url) {
            return $this->pdf_url;
        }
        return null;
    }

    /**
     * Cek apakah buku ini bisa dibaca (ada PDF-nya)
     */
    public function getIsReadableAttribute(): bool
    {
        return !is_null($this->ia_identifier) || !is_null($this->pdf_url);
    }
}
