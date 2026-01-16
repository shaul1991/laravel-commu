<?php

declare(strict_types=1);

namespace App\Application\Contracts;

interface CacheInterface
{
    public function get(string $key, mixed $default = null): mixed;

    public function set(string $key, mixed $value, ?int $ttl = null): bool;

    public function delete(string $key): bool;

    public function has(string $key): bool;

    /**
     * @template T
     *
     * @param  callable(): T  $callback
     * @return T
     */
    public function remember(string $key, int $ttl, callable $callback): mixed;

    public function forget(string $key): bool;

    public function flush(): bool;

    /**
     * @param  string[]  $keys
     */
    public function deleteMany(array $keys): bool;

    /**
     * Add tags to the cache operation.
     *
     * @param  string[]  $tags
     */
    public function tags(array $tags): self;
}
