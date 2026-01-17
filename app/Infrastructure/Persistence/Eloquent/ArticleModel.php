<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use Database\Factories\ArticleModelFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ArticleModel extends Model
{
    /** @use HasFactory<ArticleModelFactory> */
    use HasFactory, SoftDeletes;

    protected static function newFactory(): ArticleModelFactory
    {
        return ArticleModelFactory::new();
    }

    protected $table = 'articles';

    protected $fillable = [
        'uuid',
        'author_id',
        'title',
        'slug',
        'content_markdown',
        'content_html',
        'category',
        'status',
        'view_count',
        'like_count',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'view_count' => 'integer',
            'like_count' => 'integer',
            'published_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'author_id');
    }

    public function likedByUsers(): BelongsToMany
    {
        return $this->belongsToMany(
            UserModel::class,
            'article_likes',
            'article_id',
            'user_id'
        )->withTimestamps();
    }

    public function isLikedBy(?int $userId): bool
    {
        if ($userId === null) {
            return false;
        }

        return $this->likedByUsers()->where('user_id', $userId)->exists();
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function scopeByAuthor($query, int $authorId)
    {
        return $query->where('author_id', $authorId);
    }
}
