<?php

declare(strict_types=1);

namespace Manuxi\SuluArchiveBundle\Domain\Event\Archive;

use Manuxi\SuluArchiveBundle\Entity\Archive;
use Sulu\Bundle\ActivityBundle\Domain\Event\DomainEvent;

class CreatedEvent extends DomainEvent
{
    public function __construct(
        private Archive $archive,
        private array $payload = []
    ) {
        parent::__construct();
    }

    public function getEventType(): string
    {
        return 'created';
    }

    public function getResourceKey(): string
    {
        return Archive::RESOURCE_KEY;
    }

    public function getResourceId(): string
    {
        return $this->archive->getUuid();
    }

    public function getResourceTitle(): ?string
    {
        return $this->payload['title'] ?? null;
    }

    public function getEventPayload(): ?array
    {
        return $this->payload;
    }

    public function getArchive(): Archive
    {
        return $this->archive;
    }
}
