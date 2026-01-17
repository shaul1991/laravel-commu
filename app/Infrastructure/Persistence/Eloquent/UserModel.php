<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * @property int $id
 * @property string $uuid
 * @property string $name
 * @property string $email
 * @property string $username
 * @property string $password
 * @property string|null $bio
 * @property string|null $avatar_url
 * @property \Carbon\Carbon|null $email_verified_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class UserModel extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

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
        ];
    }
}
