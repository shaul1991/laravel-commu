<?php

declare(strict_types=1);

namespace App\Application\Contracts;

interface TransactionInterface
{
    /**
     * @template T
     *
     * @param  callable(): T  $callback
     * @return T
     */
    public function execute(callable $callback): mixed;

    public function begin(): void;

    public function commit(): void;

    public function rollback(): void;
}
