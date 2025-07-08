<?php

namespace App\Shared\Domain\Event;

class EventBus
{
    /** @var DomainEvent[] */
    private array $events = [];

    /** @var EventBus */
    private static ?EventBus $instance = null;

    // Private constructor to prevent direct creation
    private function __construct() {}

    /**
     * Get the single instance of EventBus.
     *
     * @return EventBus
     */
    public static function getInstance(): EventBus
    {
        if (self::$instance === null) {
            self::$instance = new EventBus();
        }

        return self::$instance;
    }

    /**
     * Record a new domain event.
     *
     * @param DomainEvent $event
     */
    public function recordEvent(DomainEvent $event): void
    {
        $this->events[] = $event;
    }

    /**
     * Dispatch all domain events.
     */
    public function dispatchEvents(): void
    {
        foreach ($this->events as $event) {
            echo "Event Dispatched: " . $event->getName() . "\n";
        }

        // Clear events after dispatching
        $this->events = [];
    }
}
