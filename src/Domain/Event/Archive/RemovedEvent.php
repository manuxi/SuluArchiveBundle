<?php

declare(strict_types=1);

namespace Manuxi\SuluArchiveBundle\Domain\Event\Archive;

use Manuxi\SuluArchiveBundle\Entity\Archive;
use Sulu\Bundle\ActivityBundle\Domain\Event\DomainEvent;

class RemovedEvent extends DomainEvent
{
    public function __construct(
        private string $archiveUuid,
        private string $archiveTitle = ''
    ) {
        parent::__construct();
    }

    public function getEventType(): string
    {
        return 'removed';
    }

    public function getResourceKey(): string
    {
        return Archive::RESOURCE_KEY;
    }

    public function getResourceId(): string
    {
        return $this->archiveUuid;
    }

    public function getResourceTitle(): ?string
    {
        return $this->archiveTitle;
    }
}
