<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Core\User\Entities\User;
use App\Domain\Core\User\Repositories\UserRepositoryInterface;
use App\Domain\Core\User\ValueObjects\Email;
use App\Domain\Core\User\ValueObjects\Password;
use App\Domain\Core\User\ValueObjects\UserId;
use App\Domain\Core\User\ValueObjects\Username;
use DateTimeImmutable;

final class EloquentUserRepository implements UserRepositoryInterface
{
    public function find(UserId $id): ?User
    {
        $model = UserModel::where('uuid', $id->value())->first();

        if (! $model) {
            return null;
        }

        return $this->toEntity($model);
    }

    public function findByEmail(Email $email): ?User
    {
        $model = UserModel::where('email', $email->value())->first();

        if (! $model) {
            return null;
        }

        return $this->toEntity($model);
    }

    public function findByUsername(Username $username): ?User
    {
        $model = UserModel::where('username', $username->value())->first();

        if (! $model) {
            return null;
        }

        return $this->toEntity($model);
    }

    public function findByIds(array $ids): array
    {
        $uuids = array_map(fn (UserId $id) => $id->value(), $ids);

        return UserModel::whereIn('uuid', $uuids)
            ->get()
            ->map(fn (UserModel $model) => $this->toEntity($model))
            ->all();
    }

    public function save(User $user): void
    {
        $model = UserModel::where('uuid', $user->id()->value())->first();

        $data = [
            'uuid' => $user->id()->value(),
            'name' => $user->name(),
            'email' => $user->email()->value(),
            'username' => $user->username()->value(),
            'password' => $user->password()->value(),
            'bio' => $user->bio(),
            'avatar_url' => $user->avatarUrl(),
            'email_verified_at' => $user->emailVerifiedAt(),
        ];

        if ($model) {
            $model->update($data);
        } else {
            UserModel::create($data);
        }
    }

    public function delete(User $user): void
    {
        UserModel::where('uuid', $user->id()->value())->delete();
    }

    public function existsByEmail(Email $email): bool
    {
        return UserModel::where('email', $email->value())->exists();
    }

    public function existsByUsername(Username $username): bool
    {
        return UserModel::where('username', $username->value())->exists();
    }

    public function search(string $keyword, int $limit = 10): array
    {
        return UserModel::where('name', 'LIKE', "%{$keyword}%")
            ->orWhere('username', 'LIKE', "%{$keyword}%")
            ->limit($limit)
            ->get()
            ->map(fn (UserModel $model) => $this->toEntity($model))
            ->all();
    }

    private function toEntity(UserModel $model): User
    {
        return User::reconstitute(
            id: UserId::fromString($model->uuid),
            email: new Email($model->email),
            username: new Username($model->username),
            password: Password::fromHash($model->password),
            name: $model->name,
            bio: $model->bio,
            avatarUrl: $model->avatar_url,
            emailVerifiedAt: $model->email_verified_at
                ? new DateTimeImmutable($model->email_verified_at->toDateTimeString())
                : null,
            createdAt: new DateTimeImmutable($model->created_at->toDateTimeString()),
            updatedAt: new DateTimeImmutable($model->updated_at->toDateTimeString())
        );
    }
}
