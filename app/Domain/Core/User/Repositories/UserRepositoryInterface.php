<?php

declare(strict_types=1);

namespace App\Domain\Core\User\Repositories;

use App\Domain\Core\User\Entities\User;
use App\Domain\Core\User\ValueObjects\Email;
use App\Domain\Core\User\ValueObjects\UserId;
use App\Domain\Core\User\ValueObjects\Username;

interface UserRepositoryInterface
{
    public function find(UserId $id): ?User;

    public function findByEmail(Email $email): ?User;

    public function findByUsername(Username $username): ?User;

    /**
     * @param  UserId[]  $ids
     * @return User[]
     */
    public function findByIds(array $ids): array;

    public function save(User $user): void;

    public function delete(User $user): void;

    public function existsByEmail(Email $email): bool;

    public function existsByUsername(Username $username): bool;

    /**
     * @return User[]
     */
    public function search(string $keyword, int $limit = 10): array;
}
