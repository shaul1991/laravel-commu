<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSettingsModel extends Model
{
    protected $table = 'user_settings';

    protected $fillable = [
        'user_id',
        'email_on_comment',
        'email_on_reply',
        'email_on_follow',
        'email_on_like',
        'push_enabled',
    ];

    protected function casts(): array
    {
        return [
            'email_on_comment' => 'boolean',
            'email_on_reply' => 'boolean',
            'email_on_follow' => 'boolean',
            'email_on_like' => 'boolean',
            'push_enabled' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'user_id');
    }
}
