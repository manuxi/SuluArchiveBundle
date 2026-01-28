<?php

declare(strict_types=1);

namespace Manuxi\SuluArchiveBundle\Search;

use CmSig\Seal\EngineInterface;
use Manuxi\SuluArchiveBundle\Domain\Event\Archive\CreatedEvent;
use Manuxi\SuluArchiveBundle\Domain\Event\Archive\ModifiedEvent;
use Manuxi\SuluArchiveBundle\Domain\Event\Archive\PublishedEvent;
use Manuxi\SuluArchiveBundle\Domain\Event\Archive\RemovedEvent;
use Manuxi\SuluArchiveBundle\Domain\Event\Archive\UnpublishedEvent;
use Manuxi\SuluArchiveBundle\Entity\ArchiveDimensionContent;
use Sulu\Component\Webspace\Manager\WebspaceManagerInterface;
use Sulu\Content\Application\ContentAggregator\ContentAggregatorInterface;
use Sulu\Content\Domain\Model\DimensionContentInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Event subscriber for updating search index when archives change (placeholder).
 */
class ArchiveSearchListener implements EventSubscriberInterface
{
    public function __construct(
        private EngineInterface $engine,
        private WebspaceManagerInterface $webspaceManager,
        private ContentAggregatorInterface $contentAggregator,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CreatedEvent::class => 'onArchiveCreated',
            ModifiedEvent::class => 'onArchiveModified',
            PublishedEvent::class => 'onArchivePublished',
            UnpublishedEvent::class => 'onArchiveUnpublished',
            RemovedEvent::class => 'onArchiveRemoved',
        ];
    }

    public function onArchiveCreated(CreatedEvent $event): void
    {
        $this->updateAdminIndex($event->getArchive());
    }

    public function onArchiveModified(ModifiedEvent $event): void
    {
        $this->updateAdminIndex($event->getArchive());
    }

    public function onArchivePublished(PublishedEvent $event): void
    {
        $this->updateAdminIndex($event->getArchive());
        $this->updateWebsiteIndex($event->getArchive());
    }

    public function onArchiveUnpublished(UnpublishedEvent $event): void
    {
        $this->removeFromWebsiteIndex($event->getArchive());
    }

    public function onArchiveRemoved(RemovedEvent $event): void
    {
        // Remove from both indexes - implementation depends on search engine
    }

    private function updateAdminIndex(object $archive): void
    {
        // Placeholder: Implement based on your search engine (SEAL/Elasticsearch/etc.)
    }

    private function updateWebsiteIndex(object $archive): void
    {
        // Placeholder: Implement based on your search engine
    }

    private function removeFromWebsiteIndex(object $archive): void
    {
        // Placeholder: Implement based on your search engine
    }
}
