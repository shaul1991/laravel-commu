<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

/**
 * @property int $id
 * @property string $uuid
 * @property string $name
 * @property string $email
 * @property string $username
 * @property string $password
 * @property string|null $bio
 * @property string|null $avatar_url
 * @property int $follower_count
 * @property int $following_count
 * @property \Carbon\Carbon|null $email_verified_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class UserModel extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }

    protected $table = 'users';

    protected $fillable = [
        'uuid',
        'name',
        'email',
        'username',
        'password',
        'bio',
        'avatar_url',
        'email_verified_at',
        'follower_count',
        'following_count',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'follower_count' => 'integer',
            'following_count' => 'integer',
        ];
    }

    public function articles(): HasMany
    {
        return $this->hasMany(ArticleModel::class, 'author_id');
    }

    public function followers(): BelongsToMany
    {
        return $this->belongsToMany(
            self::class,
            'follows',
            'following_id',
            'follower_id'
        )->withTimestamps();
    }

    public function following(): BelongsToMany
    {
        return $this->belongsToMany(
            self::class,
            'follows',
            'follower_id',
            'following_id'
        )->withTimestamps();
    }

    public function isFollowedBy(?int $userId): bool
    {
        if ($userId === null) {
            return false;
        }

        return $this->followers()->where('follower_id', $userId)->exists();
    }

    public function settings(): HasOne
    {
        return $this->hasOne(UserSettingsModel::class, 'user_id');
    }

    public function socialAccounts(): HasMany
    {
        return $this->hasMany(SocialAccountModel::class, 'user_id');
    }

    /**
     * Check if user has a usable password (not OAuth-only user).
     * OAuth-only users have a password that is a hash of empty string.
     */
    public function hasUsablePassword(): bool
    {
        if ($this->password === null || $this->password === '') {
            return false;
        }

        // Check if password is hash of empty string (OAuth-only user marker)
        return ! password_verify('', $this->password);
    }
}
