<?php

namespace App\Shared\Domain\Event;

class GenericEvent implements DomainEvent
{
    private string $name;
    private string $description;
    private ?array $payload;

    public function __construct(string $name, string $description, ?array $payload = null)
    {
        $this->name = $name;
        $this->description = $description;
        $this->payload = $payload;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getPayload(): ?array
    {
        return $this->payload;
    }
}
