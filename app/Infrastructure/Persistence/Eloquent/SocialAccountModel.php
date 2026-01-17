<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Crypt;

/**
 * @property int $id
 * @property int $user_id
 * @property string $provider
 * @property string $provider_id
 * @property string|null $provider_email
 * @property string|null $nickname
 * @property string|null $avatar_url
 * @property string|null $access_token
 * @property string|null $refresh_token
 * @property \Carbon\Carbon|null $token_expires_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 * @property-read UserModel $user
 */
class SocialAccountModel extends Model
{
    use SoftDeletes;

    protected $table = 'social_accounts';

    protected $fillable = [
        'user_id',
        'provider',
        'provider_id',
        'provider_email',
        'nickname',
        'avatar_url',
        'access_token',
        'refresh_token',
        'token_expires_at',
    ];

    protected $hidden = [
        'access_token',
        'refresh_token',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'token_expires_at' => 'datetime',
        ];
    }

    /**
     * Get the user that owns the social account.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'user_id');
    }

    /**
     * Encrypt access_token when setting.
     */
    public function setAccessTokenAttribute(?string $value): void
    {
        $this->attributes['access_token'] = $value !== null ? Crypt::encryptString($value) : null;
    }

    /**
     * Decrypt access_token when getting.
     */
    public function getAccessTokenAttribute(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        try {
            return Crypt::decryptString($value);
        } catch (\Exception) {
            return $value;
        }
    }

    /**
     * Encrypt refresh_token when setting.
     */
    public function setRefreshTokenAttribute(?string $value): void
    {
        $this->attributes['refresh_token'] = $value !== null ? Crypt::encryptString($value) : null;
    }

    /**
     * Decrypt refresh_token when getting.
     */
    public function getRefreshTokenAttribute(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        try {
            return Crypt::decryptString($value);
        } catch (\Exception) {
            return $value;
        }
    }
}
