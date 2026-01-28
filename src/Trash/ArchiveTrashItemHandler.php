<?php

declare(strict_types=1);

namespace Manuxi\SuluArchiveBundle\Trash;

use Doctrine\Common\Collections\ArrayCollection;
use Manuxi\SuluArchiveBundle\Admin\ArchiveAdmin;
use Manuxi\SuluArchiveBundle\Application\Mapper\ArchiveMapperInterface;
use Manuxi\SuluArchiveBundle\Domain\Event\Archive\RestoredEvent;
use Manuxi\SuluArchiveBundle\Entity\Archive;
use Manuxi\SuluArchiveBundle\Entity\ArchiveDimensionContent;
use Manuxi\SuluArchiveBundle\Repository\ArchiveRepository;
use Sulu\Bundle\ActivityBundle\Application\Collector\DomainEventCollectorInterface;
use Sulu\Bundle\TrashBundle\Application\RestoreConfigurationProvider\RestoreConfiguration;
use Sulu\Bundle\TrashBundle\Application\RestoreConfigurationProvider\RestoreConfigurationProviderInterface;
use Sulu\Bundle\TrashBundle\Application\TrashItemHandler\RestoreTrashItemHandlerInterface;
use Sulu\Bundle\TrashBundle\Application\TrashItemHandler\StoreTrashItemHandlerInterface;
use Sulu\Bundle\TrashBundle\Domain\Model\TrashItemInterface;
use Sulu\Bundle\TrashBundle\Domain\Repository\TrashItemRepositoryInterface;
use Sulu\Content\Application\ContentMerger\ContentMergerInterface;
use Sulu\Content\Application\ContentNormalizer\ContentNormalizerInterface;
use Sulu\Content\Domain\Model\DimensionContentCollection;
use Sulu\Content\Domain\Model\DimensionContentInterface;
use Webmozart\Assert\Assert;

class ArchiveTrashItemHandler implements StoreTrashItemHandlerInterface, RestoreTrashItemHandlerInterface, RestoreConfigurationProviderInterface
{
    /**
     * @param iterable<ArchiveMapperInterface> $archiveMappers
     */
    public function __construct(
        private readonly TrashItemRepositoryInterface $trashItemRepository,
        private readonly ArchiveRepository $archiveRepository,
        private readonly ContentNormalizerInterface $contentNormalizer,
        private readonly ContentMergerInterface $contentMerger,
        private readonly iterable $archiveMappers,
        private readonly DomainEventCollectorInterface $domainEventCollector,
    ) {
    }

    public static function getResourceKey(): string
    {
        return Archive::RESOURCE_KEY;
    }

    public function store(object $resource, array $options = []): TrashItemInterface
    {
        Assert::isInstanceOf($resource, Archive::class);
        $archive = $resource;

        $data = [
            'id' => $archive->getId(),
        ];

        $titles = [];
        $restoreType = null;

        /** @var ArrayCollection<int, ArchiveDimensionContent> $allDimensionContents */
        $allDimensionContents = $archive->getDimensionContents();

        /** @var array<ArchiveDimensionContent> $localizedDimensionContents */
        $localizedDimensionContents = $allDimensionContents
            ->filter(
                static fn(ArchiveDimensionContent $dimensionContent) => null !== $dimensionContent->getLocale()
                && DimensionContentInterface::STAGE_DRAFT === $dimensionContent->getStage()
                && DimensionContentInterface::CURRENT_VERSION === $dimensionContent->getVersion()
            )
            ->toArray();

        $localizedDimensionContents = \array_combine(
            \array_map(static fn(ArchiveDimensionContent $dc) => $dc->getLocale(), $localizedDimensionContents),
            $localizedDimensionContents
        );

        /** @var ArchiveDimensionContent|null $unlocalizedDimensionContent */
        $unlocalizedDimensionContent = $allDimensionContents
            ->filter(
                static fn(ArchiveDimensionContent $dimensionContent) => null === $dimensionContent->getLocale()
                && DimensionContentInterface::STAGE_DRAFT === $dimensionContent->getStage()
                && DimensionContentInterface::CURRENT_VERSION === $dimensionContent->getVersion()
            )
            ->first() ?: null;

        Assert::notNull($unlocalizedDimensionContent, 'Expected to find unlocalized dimension content for the archive.');
        Assert::notEmpty($localizedDimensionContents, 'Expected to find at least one localized dimension content for the archive.');

        $availableLocales = $unlocalizedDimensionContent->getAvailableLocales();
        Assert::isArray($availableLocales, 'Expected availableLocales to be an array');
        /** @var array<string, ArchiveDimensionContent> $localizedDimensionContents */
        $localizedDimensionContents = \array_merge(
            \array_flip(
                \array_filter(
                    $availableLocales,
                    static fn($locale) => \array_key_exists($locale, $localizedDimensionContents)
                )
            ),
            $localizedDimensionContents,
        );

        $data['dimensionContents'] = [];
        foreach ($localizedDimensionContents as $locale => $localizedDimensionContent) {
            $mergedDimensionContent = $this->contentMerger->merge(
                new DimensionContentCollection(
                    new ArrayCollection([$unlocalizedDimensionContent, $localizedDimensionContent]),
                    [
                        'locale' => $locale,
                        'stage' => DimensionContentInterface::STAGE_DRAFT,
                        'version' => DimensionContentInterface::CURRENT_VERSION,
                    ],
                    ArchiveDimensionContent::class,
                ),
            );

            $normalizedContent = $this->contentNormalizer->normalize($mergedDimensionContent);
            $data['dimensionContents'][] = $normalizedContent;

            $title = $localizedDimensionContent->getTitle();
            if ($title) {
                $titles[$locale] = $title;
            }
        }

        return $this->trashItemRepository->create(
            Archive::RESOURCE_KEY,
            (string) $archive->getId(),
            $titles,
            $data,
            $restoreType,
            $options,
            Archive::SECURITY_CONTEXT,
            null,
            null
        );
    }

    public function restore(TrashItemInterface $trashItem, array $restoreFormData = []): object
    {
        $restoreData = $trashItem->getRestoreData();
        $archiveUuid = $trashItem->getResourceId();

        $archive = $this->archiveRepository->findByUuid($archiveUuid);
        if (!$archive) {
            $archive = new Archive();
            $this->archiveRepository->add($archive);
        }

        $dimensionContents = $restoreData['dimensionContents'] ?? [];
        $archiveTitle = null;

        Assert::isArray($dimensionContents, 'Expected dimensionContents to be an array');
        foreach ($dimensionContents as $dimensionContentData) {
            Assert::isArray($dimensionContentData, 'Expected dimensionContentData to be an array');

            if (!$archiveTitle && \array_key_exists('title', $dimensionContentData) && $dimensionContentData['title']) {
                /** @var string $archiveTitle */
                $archiveTitle = $dimensionContentData['title'];
            }

            foreach ($this->archiveMappers as $archiveMapper) {
                $archiveMapper->mapArchiveData($archive, $dimensionContentData);
            }
        }

        $this->domainEventCollector->collect(
            new RestoredEvent($archive, $restoreData)
        );

        return $archive;
    }

    public function getConfiguration(): RestoreConfiguration
    {
        return new RestoreConfiguration(
            null,
            ArchiveAdmin::EDIT_FORM_VIEW,
            ['id' => 'id', 'locale' => 'locale']
        );
    }
}
