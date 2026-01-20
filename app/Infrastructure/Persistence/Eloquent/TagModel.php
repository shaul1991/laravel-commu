<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TagModel extends Model
{
    use SoftDeletes;

    protected $table = 'tags';

    protected $fillable = [
        'uuid',
        'name',
        'slug',
        'article_count',
    ];

    protected function casts(): array
    {
        return [
            'article_count' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    public function articles(): BelongsToMany
    {
        return $this->belongsToMany(
            ArticleModel::class,
            'article_tags',
            'tag_id',
            'article_id'
        )->withTimestamps();
    }

    public function scopePopular($query, int $limit = 10)
    {
        return $query->orderByDesc('article_count')->limit($limit);
    }

    public function scopeSearch($query, string $keyword)
    {
        // Use LIKE for SQLite compatibility, ILIKE for PostgreSQL
        $driver = $query->getConnection()->getDriverName();
        $operator = $driver === 'pgsql' ? 'ILIKE' : 'LIKE';

        return $query->where('name', $operator, "%{$keyword}%");
    }

    public function incrementArticleCount(): void
    {
        $this->increment('article_count');
    }

    public function decrementArticleCount(): void
    {
        if ($this->article_count > 0) {
            $this->decrement('article_count');
        }
    }
}
