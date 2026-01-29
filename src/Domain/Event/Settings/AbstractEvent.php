<?php

declare(strict_types=1);

namespace Manuxi\SuluArchiveBundle\Domain\Event\Settings;

use Manuxi\SuluArchiveBundle\Entity\ArchiveSettings;
use Sulu\Bundle\ActivityBundle\Domain\Event\DomainEvent;

abstract class AbstractEvent extends DomainEvent
{
    private array $payload = [];

    public function __construct(
        private ArchiveSettings $entity,
        array $payload = []
    ) {
        parent::__construct();
        $this->payload = $payload;
    }

    public function getEvent(): ArchiveSettings
    {
        return $this->entity;
    }

    public function getEventPayload(): ?array
    {
        return $this->payload;
    }

    public function getResourceKey(): string
    {
        return ArchiveSettings::RESOURCE_KEY;
    }

    public function getResourceId(): string
    {
        return (string) $this->entity->getId();
    }

    public function getResourceTitle(): ?string
    {
        return "Archive Settings";
    }

    public function getResourceSecurityContext(): ?string
    {
        return ArchiveSettings::SECURITY_CONTEXT;
    }
}
