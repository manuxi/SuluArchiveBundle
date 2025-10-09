<?php

declare(strict_types=1);

namespace Manuxi\SuluArchiveBundle\Search;

use Manuxi\SuluArchiveBundle\Search\Event\ArchivePublishedEvent;
use Manuxi\SuluArchiveBundle\Search\Event\ArchiveRemovedEvent;
use Manuxi\SuluArchiveBundle\Search\Event\ArchiveSavedEvent;
use Manuxi\SuluArchiveBundle\Search\Event\ArchiveUnpublishedEvent;
use Massive\Bundle\SearchBundle\Search\SearchManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ArchiveSearchSubscriber implements EventSubscriberInterface
{

    public function __construct(private SearchManagerInterface $searchManager) {}

    public static function getSubscribedEvents(): array
    {
        return [
            ArchivePublishedEvent::class => 'onPublished',
            ArchiveUnpublishedEvent::class => 'onUnpublished',
            ArchiveSavedEvent::class => 'onSaved',
            ArchiveRemovedEvent::class => 'onRemoved',
        ];
    }

    public function onPublished(ArchivePublishedEvent $event): void
    {
        $entity = $event->getEntity();
        if($entity->isPublished()) {
            $this->searchManager->index($entity);
        }
    }

    public function onUnpublished(ArchiveUnpublishedEvent $event): void
    {
        $this->searchManager->deindex($event->getEntity());
    }

    public function onSaved(ArchiveSavedEvent $event): void
    {
        $entity = $event->getEntity();
        if($entity->isPublished()) {
            $this->searchManager->index($entity);
        } else {
            $this->searchManager->deindex($entity);
        }
    }

    public function onRemoved(ArchiveRemovedEvent $event): void
    {
        $this->searchManager->deindex($event->getEntity());
    }
}