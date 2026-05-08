<?php

declare(strict_types=1);

namespace App\Projectors;

use Illuminate\Support\Collection;
use Spatie\BetterTypes\Handlers;
use Spatie\BetterTypes\Method;
use Spatie\EventSourcing\EventHandlers\Projectors\Projector;
use Spatie\EventSourcing\StoredEvents\StoredEvent;

abstract class BaseProjector extends Projector
{
    /**
     * Override handle() to also pass StoredEvent as second argument to handler methods.
     * This allows projector methods to have the signature:
     *   public function onXxx(XxxEvent $event, StoredEvent $storedEvent): void
     */
    public function handle(StoredEvent $storedEvent): void
    {
        $event = $storedEvent->event;

        /** @var Collection<int, Method> $methods */
        $methods = collect(
            Handlers::new($this)
                ->public()
                ->protected()
                ->reject(fn (Method $method) => $method->accepts(null))
                ->all()
        );

        $methods->each(function (Method $method) use ($event, $storedEvent): void {
            if ($method->accepts($event, $storedEvent)) {
                $this->{$method->getName()}($event, $storedEvent);
            } elseif ($method->accepts($event)) {
                $this->{$method->getName()}($event);
            }
        });
    }

    /**
     * Override handles() to also match methods with (Event, StoredEvent) signature.
     */
    public function handles(StoredEvent $storedEvent): bool
    {
        $event = $storedEvent->event;

        /** @var Collection<int, Method> $methods */
        $methods = collect(
            Handlers::new($this)
                ->public()
                ->protected()
                ->reject(fn (Method $method) => $method->accepts(null))
                ->all()
        );

        return $methods->filter(function (Method $method) use ($event, $storedEvent): bool {
            return $method->accepts($event, $storedEvent) || $method->accepts($event);
        })->isNotEmpty();
    }
}
