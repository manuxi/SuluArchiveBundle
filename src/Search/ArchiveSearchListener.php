<?php

declare(strict_types=1);

namespace Manuxi\SuluArchiveBundle\Search;

use CmsIg\Seal\EngineInterface;
use Manuxi\SuluArchiveBundle\Domain\Event\Archive\CreatedEvent;
use Manuxi\SuluArchiveBundle\Domain\Event\Archive\ModifiedEvent;
use Manuxi\SuluArchiveBundle\Domain\Event\Archive\PublishedEvent;
use Manuxi\SuluArchiveBundle\Domain\Event\Archive\RemovedEvent;
use Manuxi\SuluArchiveBundle\Domain\Event\Archive\UnpublishedEvent;
use Manuxi\SuluArchiveBundle\Entity\Archive;
use Manuxi\SuluArchiveBundle\Entity\ArchiveDimensionContent;
use Sulu\Component\Webspace\Manager\WebspaceManagerInterface;
use Sulu\Content\Application\ContentAggregator\ContentAggregatorInterface;
use Sulu\Content\Domain\Model\DimensionContentInterface;
use Sulu\Content\Domain\Model\WorkflowInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ArchiveSearchListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly EngineInterface $engine,
        private readonly WebspaceManagerInterface $webspaceManager,
        private readonly ContentAggregatorInterface $contentAggregator,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CreatedEvent::class => 'onCreatedOrModified',
            ModifiedEvent::class => 'onCreatedOrModified',
            PublishedEvent::class => 'onPublished',
            UnpublishedEvent::class => 'onUnpublished',
            RemovedEvent::class => 'onRemoved',
        ];
    }

    public function onCreatedOrModified(CreatedEvent|ModifiedEvent $domainEvent): void
    {
        $archive = $domainEvent->getArchive();

        foreach ($this->getLocales() as $locale) {
            /** @var ArchiveDimensionContent $dimensionContent */
            $dimensionContent = $this->contentAggregator->aggregate(
                $archive,
                [
                    'locale' => $locale,
                    'stage' => DimensionContentInterface::STAGE_DRAFT,
                    'version' => DimensionContentInterface::CURRENT_VERSION,
                ]
            );

            if (!$dimensionContent->getTitle()) {
                continue;
            }

            // Update admin index only (draft changes)
            $this->indexForAdmin($archive, $dimensionContent, $locale);
        }
    }

    public function onPublished(PublishedEvent $domainEvent): void
    {
        $archive = $domainEvent->getArchive();

        foreach ($this->getLocales() as $locale) {
            /** @var ArchiveDimensionContent $dimensionContent */
            $dimensionContent = $this->contentAggregator->aggregate(
                $archive,
                [
                    'locale' => $locale,
                    'stage' => DimensionContentInterface::STAGE_LIVE,
                    'version' => DimensionContentInterface::CURRENT_VERSION,
                ]
            );

            if (!$dimensionContent->getTitle()) {
                continue;
            }

            // Update both indexes
            $this->indexForAdmin($archive, $dimensionContent, $locale);
            $this->indexForWebsite($archive, $dimensionContent, $locale);
        }
    }

    public function onUnpublished(UnpublishedEvent $domainEvent): void
    {
        $archive = $domainEvent->getArchive();

        foreach ($this->getLocales() as $locale) {
            /** @var ArchiveDimensionContent $dimensionContent */
            $dimensionContent = $this->contentAggregator->aggregate(
                $archive,
                [
                    'locale' => $locale,
                    'stage' => DimensionContentInterface::STAGE_DRAFT,
                    'version' => DimensionContentInterface::CURRENT_VERSION,
                ]
            );

            if (!$dimensionContent->getTitle()) {
                continue;
            }

            // Update admin index
            $this->indexForAdmin($archive, $dimensionContent, $locale);

            // Remove from website index
            $documentId = $this->getDocumentId($archive, $locale);
            $this->engine->deleteDocument('website', $documentId);
        }
    }

    public function onRemoved(RemovedEvent $domainEvent): void
    {
        // Remove from all locale variants in both indexes
        foreach ($this->getLocales() as $locale) {
            $documentId = 'archive-' . $domainEvent->getResourceId() . '-' . $locale;
            $this->engine->deleteDocument('admin', $documentId);
            $this->engine->deleteDocument('website', $documentId);
        }
    }

    private function indexForAdmin(Archive $archive, ArchiveDimensionContent $dimensionContent, string $locale): void
    {
        $content = array_filter([
            $dimensionContent->getSubtitle(),
            $dimensionContent->getSummary(),
            $dimensionContent->getText(),
            $dimensionContent->getFooter(),
        ]);

        $this->engine->saveDocument('admin', [
            'id' => $this->getDocumentId($archive, $locale),
            'resourceKey' => Archive::RESOURCE_KEY,
            'resourceId' => (string) $archive->getId(),
            'locale' => $locale,
            'securityContext' => Archive::SECURITY_CONTEXT,
            'title' => $dimensionContent->getTitle() ?? '',
            'content' => array_values($content),
            'mediaId' => $dimensionContent->getImage()?->getId(),
            'changedAt' => $dimensionContent->getChanged()?->format('c'),
            'createdAt' => $dimensionContent->getCreated()?->format('c'),
            'published' => WorkflowInterface::WORKFLOW_PLACE_PUBLISHED === $dimensionContent->getWorkflowPlace() ? '1' : '0',
            'workflowPlace' => $dimensionContent->getWorkflowPlace(),
        ]);
    }

    private function indexForWebsite(Archive $archive, ArchiveDimensionContent $dimensionContent, string $locale): void
    {
        $content = array_filter([
            $dimensionContent->getSubtitle(),
            $dimensionContent->getSummary(),
            $dimensionContent->getText(),
            $dimensionContent->getFooter(),
        ]);

        $this->engine->saveDocument('website', [
            'id' => $this->getDocumentId($archive, $locale),
            'resourceKey' => Archive::RESOURCE_KEY,
            'resourceId' => (string) $archive->getId(),
            'locale' => $locale,
            'webspaces' => [],
            'title' => $dimensionContent->getTitle() ?? '',
            'url' => $dimensionContent->getRoute()?->getSlug() ?? '',
            'content' => $content,
            'mediaId' => $dimensionContent->getImage()?->getId(),
        ]);
    }

    private function getDocumentId(Archive $archive, string $locale): string
    {
        return 'archive-' . $archive->getId() . '-' . $locale;
    }

    private function getLocales(): array
    {
        $locales = [];
        foreach ($this->webspaceManager->getWebspaceCollection() as $webspace) {
            foreach ($webspace->getAllLocalizations() as $localization) {
                $locales[$localization->getLocale()] = true;
            }
        }

        return array_keys($locales);
    }
}
