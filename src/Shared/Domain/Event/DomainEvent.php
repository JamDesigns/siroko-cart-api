<?php

namespace App\Shared\Domain\Event;

interface DomainEvent
{
    public function getName(): string;
    public function getDescription(): string;
    public function getPayload(): ?array;
}
