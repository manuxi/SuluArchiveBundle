<?php

declare(strict_types=1);

namespace Manuxi\SuluArchiveBundle\Teaser;

use Manuxi\SuluArchiveBundle\Entity\Archive;
use Manuxi\SuluArchiveBundle\Entity\ArchiveDimensionContent;
use Manuxi\SuluArchiveBundle\Repository\ArchiveRepository;
use Sulu\Bundle\AdminBundle\Teaser\Configuration\TeaserConfiguration;
use Sulu\Bundle\AdminBundle\Teaser\Provider\TeaserProviderInterface;
use Sulu\Bundle\AdminBundle\Teaser\Teaser;
use Sulu\Content\Application\ContentAggregator\ContentAggregatorInterface;
use Sulu\Content\Application\ContentEnhancer\ContentEnhancerInterface;
use Sulu\Content\Domain\Exception\ContentNotFoundException;
use Sulu\Content\Domain\Model\DimensionContentInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ArchiveTeaserProvider implements TeaserProviderInterface
{
    public function __construct(
        protected ArchiveRepository $archiveRepository,
        protected ContentAggregatorInterface $contentAggregator,
        protected ContentEnhancerInterface $contentEnhancer,
        protected TranslatorInterface $translator,
    ) {
    }

    public function getConfiguration(): TeaserConfiguration
    {
        return new TeaserConfiguration(
            $this->translator->trans('sulu_archive.archives', [], 'admin'),
            Archive::RESOURCE_KEY,
            'table',
            ['title'],
            $this->translator->trans('sulu_archive.select_archive', [], 'admin'),
        );
    }

    /**
     * @param array<string> $ids
     *
     * @return Teaser[]
     */
    public function find(array $ids, $locale): array
    {
        if (0 === \count($ids)) {
            return [];
        }

        $archives = $this->findArchivesByUuids($ids, $locale);

        $teasers = [];
        foreach ($archives as $archive) {
            $teaser = $this->createTeaserFromArchive($archive, $locale);
            if (null !== $teaser) {
                $teasers[] = $teaser;
            }
        }

        return $teasers;
    }

    /**
     * @param array<string> $uuids
     *
     * @return array<Archive>
     */
    private function findArchivesByUuids(array $uuids, string $locale): array
    {
        /** @var array<Archive> $archives */
        $archives = $this->archiveRepository->findByFilters(
            filters: [
                'uuids' => $uuids,
                'locale' => $locale,
                'stage' => DimensionContentInterface::STAGE_LIVE,
            ],
            sortBys: [],
            selects: [
                ArchiveRepository::GROUP_SELECT_ARCHIVE_WEBSITE => true,
            ]
        );

        $uuidPositions = \array_flip($uuids);
        \usort(
            $archives,
            static fn(Archive $a, Archive $b) => ($uuidPositions[$a->getUuid()] ?? 0) - ($uuidPositions[$b->getUuid()] ?? 0)
        );

        return $archives;
    }

    private function createTeaserFromArchive(Archive $archive, string $locale): ?Teaser
    {
        try {
            /** @var ArchiveDimensionContent|null $dimensionContent */
            $dimensionContent = $this->contentAggregator->aggregate(
                $archive,
                [
                    'locale' => $locale,
                    'stage' => DimensionContentInterface::STAGE_LIVE,
                    'version' => DimensionContentInterface::CURRENT_VERSION,
                ]
            );

            if (null === $dimensionContent) {
                return null;
            }

            $enhancedContent = $this->contentEnhancer->enhance($dimensionContent);
            if ($enhancedContent instanceof ArchiveDimensionContent) {
                $dimensionContent = $enhancedContent;
            }
        } catch (ContentNotFoundException) {
            return null;
        }

        $title = $this->resolveTitle($dimensionContent);
        if (null === $title) {
            return null;
        }

        return new Teaser(
            $archive->getUuid(),
            Archive::RESOURCE_KEY,
            $locale,
            $title,
            $this->resolveDescription($dimensionContent),
            $this->resolveMoreText($dimensionContent),
            $this->resolveUrl($dimensionContent),
            $this->resolveMediaId($dimensionContent),
            $this->getAttributes($dimensionContent)
        );
    }

    protected function resolveUrl(ArchiveDimensionContent $dimensionContent): ?string
    {
        $route = $dimensionContent->getRoute();
        $url = $route?->getSlug() ?? null;

        return \is_string($url) ? $url : null;
    }

    protected function resolveTitle(ArchiveDimensionContent $dimensionContent): ?string
    {
        $title = $dimensionContent->getExcerptTitle() ?? $dimensionContent->getTitle();

        return \is_string($title) && '' !== $title ? $title : null;
    }

    protected function resolveDescription(ArchiveDimensionContent $dimensionContent): ?string
    {
        $description = $dimensionContent->getSummary();
        if (!empty($description)) {
            return \strip_tags($description);
        }

        $text = $dimensionContent->getText();
        if (!empty($text)) {
            return \mb_substr(\strip_tags($text), 0, 200);
        }

        $excerptDescription = $dimensionContent->getExcerptDescription();
        if (!empty($excerptDescription)) {
            return \strip_tags($excerptDescription);
        }

        return null;
    }

    protected function resolveMoreText(ArchiveDimensionContent $dimensionContent): ?string
    {
        $moreText = $dimensionContent->getExcerptMore();

        return '' !== ($moreText ?? '') ? $moreText : null;
    }

    protected function resolveMediaId(ArchiveDimensionContent $dimensionContent): ?int
    {
        $image = $dimensionContent->getImage();
        if (null !== $image) {
            return $image->getId();
        }

        $excerptImage = $dimensionContent->getExcerptImage();

        return $excerptImage['id'] ?? null;
    }

    /**
     * @return array<string, mixed>
     */
    protected function getAttributes(ArchiveDimensionContent $dimensionContent): array
    {
        return [];
    }
}
