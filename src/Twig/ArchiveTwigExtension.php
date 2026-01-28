<?php

declare(strict_types=1);

namespace Manuxi\SuluArchiveBundle\Twig;

use Doctrine\ORM\EntityManagerInterface;
use Manuxi\SuluArchiveBundle\Entity\Archive;
use Manuxi\SuluArchiveBundle\Entity\ArchiveDimensionContent;
use Manuxi\SuluArchiveBundle\Repository\ArchiveRepository;
use Sulu\Component\Webspace\Analyzer\RequestAnalyzerInterface;
use Sulu\Content\Application\ContentAggregator\ContentAggregatorInterface;
use Sulu\Content\Application\ContentResolver\ContentResolverInterface;
use Sulu\Content\Domain\Model\DimensionContentInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ArchiveTwigExtension extends AbstractExtension
{
    private ?ArchiveRepository $archiveRepository = null;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private readonly ContentAggregatorInterface $contentAggregator,
        private readonly ContentResolverInterface $contentResolver,
        private readonly RequestAnalyzerInterface $requestAnalyzer,
    ) {
    }

    private function getArchiveRepository(): ArchiveRepository
    {
        if (null === $this->archiveRepository) {
            $repository = $this->entityManager->getRepository(Archive::class);

            if (!$repository instanceof ArchiveRepository) {
                throw new \RuntimeException(sprintf('Expected ArchiveRepository, got %s', get_class($repository)));
            }

            $this->archiveRepository = $repository;
        }

        return $this->archiveRepository;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('sulu_get_archives', [$this, 'getArchives']),
            new TwigFunction('sulu_resolve_archive', [$this, 'resolveArchive']),
        ];
    }

    /**
     * Resolve a single archive by UUID.
     *
     * @param array<string, string> $properties
     *
     * @return array<string, mixed>|null
     */
    public function resolveArchive(string $uuid, array $properties = [], ?string $locale = null): ?array
    {
        if (null === $locale) {
            $localization = $this->requestAnalyzer->getCurrentLocalization();
            if (null === $localization) {
                return null;
            }
            $locale = $localization->getLocale();
        }

        $archive = $this->getArchiveRepository()->findByUuid($uuid);
        if (!$archive) {
            return null;
        }

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
            return null;
        }

        return $this->contentResolver->resolve($dimensionContent, $properties);
    }

    /**
     * Get multiple archives with filters.
     *
     * @param array<string, mixed>  $filters
     * @param array<string, string> $properties
     *
     * @return array<int, array<string, mixed>>
     */
    public function getArchives(
        array $filters = [],
        array $properties = [],
        ?string $locale = null,
        int $limit = 10,
    ): array {
        if (null === $locale) {
            $localization = $this->requestAnalyzer->getCurrentLocalization();
            if (null === $localization) {
                return [];
            }
            $locale = $localization->getLocale();
        }

        $filters['locale'] = $locale;
        $filters['stage'] = DimensionContentInterface::STAGE_LIVE;
        $filters['limit'] = $limit;

        $archives = $this->getArchiveRepository()->findByFilters($filters);
        $result = [];

        foreach ($archives as $archive) {
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

            $result[] = $this->contentResolver->resolve($dimensionContent, $properties);
        }

        return $result;
    }
}
