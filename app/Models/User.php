<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\Book;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // app/Models/User.php — tambahkan method ini ke class yang sudah ada

    public function books(): BelongsToMany
    {
        return $this->belongsToMany(Book::class, 'user_books')
            ->withPivot(['status', 'started_reading_at', 'finished_at'])
            ->withTimestamps();
    }

    public function wishlist(): BelongsToMany
    {
        return $this->books()->wherePivot('status', 'wishlist');
    }

    public function readingNow(): BelongsToMany
    {
        return $this->books()->wherePivot('status', 'reading');
    }

    public function finished(): BelongsToMany
    {
        return $this->books()->wherePivot('status', 'finished');
    }
}
