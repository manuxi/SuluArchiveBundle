<?php

declare(strict_types=1);

namespace Manuxi\SuluArchiveBundle\Search;

use Manuxi\SuluArchiveBundle\Entity\Archive;
use Manuxi\SuluArchiveBundle\Entity\ArchiveDimensionContent;
use Manuxi\SuluArchiveBundle\Repository\ArchiveRepository;
use Sulu\Component\Webspace\Manager\WebspaceManagerInterface;
use Sulu\Content\Application\ContentAggregator\ContentAggregatorInterface;
use Sulu\Content\Domain\Model\DimensionContentInterface;

/**
 * Admin search provider for archives (placeholder - implement based on your search engine).
 */
class ArchiveAdminSearchProvider
{
    public function __construct(
        private ArchiveRepository $archiveRepository,
        private WebspaceManagerInterface $webspaceManager,
        private ContentAggregatorInterface $contentAggregator,
    ) {
    }

    public static function getIndex(): string
    {
        return 'sulu_archive_admin';
    }

    public function getDocuments(): \Generator
    {
        $locales = $this->webspaceManager->getAllLocales();
        $archives = $this->archiveRepository->findAll();

        foreach ($archives as $archive) {
            foreach ($locales as $locale) {
                try {
                    /** @var ArchiveDimensionContent|null $dimensionContent */
                    $dimensionContent = $this->contentAggregator->aggregate(
                        $archive,
                        [
                            'locale' => $locale,
                            'stage' => DimensionContentInterface::STAGE_DRAFT,
                        ]
                    );

                    if (null === $dimensionContent || !$dimensionContent->getTitle()) {
                        continue;
                    }

                    yield [
                        'id' => $archive->getUuid() . '_' . $locale,
                        'uuid' => $archive->getUuid(),
                        'locale' => $locale,
                        'title' => $dimensionContent->getTitle(),
                        'description' => $dimensionContent->getSummary(),
                        'resourceKey' => Archive::RESOURCE_KEY,
                    ];
                } catch (\Exception) {
                    // Skip if content not found for locale
                }
            }
        }
    }
}
