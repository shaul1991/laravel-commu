<?php

declare(strict_types=1);

namespace App\Application\Contracts;

use App\Domain\Core\Shared\DomainEvent;

interface EventDispatcherInterface
{
    public function dispatch(DomainEvent $event): void;

    /**
     * @param  DomainEvent[]  $events
     */
    public function dispatchAll(array $events): void;
}
