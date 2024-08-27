<?php

declare(strict_types=1);

namespace Manuxi\SuluArchiveBundle\Domain\Event;

use Manuxi\SuluArchiveBundle\Entity\Archive;
use Sulu\Bundle\ActivityBundle\Domain\Event\DomainEvent;

class ArchiveCopiedLanguageEvent extends DomainEvent
{
    private Archive $archive;
    private array $payload = [];

    public function __construct(Archive $archive, array $payload)
    {
        parent::__construct();
        $this->archive = $archive;
        $this->payload = $payload;
    }

    public function getArchive(): Archive
    {
        return $this->archive;
    }

    public function getEventPayload(): ?array
    {
        return $this->payload;
    }

    public function getEventType(): string
    {
        return 'published';
    }

    public function getResourceKey(): string
    {
        return Archive::RESOURCE_KEY;
    }

    public function getResourceId(): string
    {
        return (string)$this->archive->getId();
    }

    public function getResourceTitle(): ?string
    {
        return $this->archive->getTitle();
    }

    public function getResourceSecurityContext(): ?string
    {
        return Archive::SECURITY_CONTEXT;
    }
}
