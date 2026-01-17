<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use Database\Factories\CommentModelFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CommentModel extends Model
{
    /** @use HasFactory<CommentModelFactory> */
    use HasFactory, SoftDeletes;

    protected static function newFactory(): CommentModelFactory
    {
        return CommentModelFactory::new();
    }

    protected $table = 'comments';

    protected $fillable = [
        'uuid',
        'article_id',
        'author_id',
        'parent_id',
        'content',
        'like_count',
        'reply_count',
        'is_deleted',
    ];

    protected function casts(): array
    {
        return [
            'like_count' => 'integer',
            'reply_count' => 'integer',
            'is_deleted' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    public function article(): BelongsTo
    {
        return $this->belongsTo(ArticleModel::class, 'article_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'author_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('created_at', 'asc');
    }

    public function likedByUsers(): BelongsToMany
    {
        return $this->belongsToMany(
            UserModel::class,
            'comment_likes',
            'comment_id',
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

    public function scopeRootComments($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeByArticle($query, int $articleId)
    {
        return $query->where('article_id', $articleId);
    }

    public function scopeNotDeleted($query)
    {
        return $query->where('is_deleted', false);
    }

    public function getDisplayContent(): string
    {
        return $this->is_deleted ? '삭제된 댓글입니다.' : $this->content;
    }
}
