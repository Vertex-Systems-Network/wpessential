<?php

declare(strict_types=1);

namespace WPEssential\Platform\Events;

final class EventBus
{
    /** @var array<string, list<callable(DomainEvent): void>> */
    private array $listeners = [];

    public function listen(string $eventName, callable $listener): void
    {
        $this->listeners[$eventName][] = $listener;
    }

    public function dispatch(DomainEvent $event): void
    {
        foreach ($this->listeners[$event->name] ?? [] as $listener) {
            $listener($event);
        }
    }
}
