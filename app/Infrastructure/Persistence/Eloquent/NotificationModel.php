<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use Database\Factories\NotificationModelFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class NotificationModel extends Model
{
    /** @use HasFactory<NotificationModelFactory> */
    use HasFactory, SoftDeletes;

    protected static function newFactory(): NotificationModelFactory
    {
        return NotificationModelFactory::new();
    }

    protected $table = 'notifications';

    protected $fillable = [
        'uuid',
        'user_id',
        'type',
        'message',
        'data',
        'is_read',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'is_read' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'user_id');
    }

    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }
}
