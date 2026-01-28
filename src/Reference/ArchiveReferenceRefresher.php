<?php

declare(strict_types=1);

namespace Manuxi\SuluArchiveBundle\Reference;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Manuxi\SuluArchiveBundle\Entity\Archive;
use Manuxi\SuluArchiveBundle\Entity\ArchiveDimensionContent;
use Sulu\Bundle\ReferenceBundle\Application\Collector\ReferenceCollector;
use Sulu\Bundle\ReferenceBundle\Application\Refresh\ReferenceRefresherInterface;
use Sulu\Bundle\ReferenceBundle\Domain\Repository\ReferenceRepositoryInterface;
use Sulu\Content\Application\ContentMerger\ContentMergerInterface;
use Sulu\Content\Application\ContentResolver\ContentViewResolver\ContentViewResolverInterface;
use Sulu\Content\Domain\Model\DimensionContentCollection;
use Sulu\Content\Domain\Model\DimensionContentInterface;

class ArchiveReferenceRefresher implements ReferenceRefresherInterface
{
    /**
     * @var EntityRepository<ArchiveDimensionContent>
     */
    private EntityRepository $archiveDimensionContentRepository;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ReferenceRepositoryInterface $referenceRepository,
        private readonly ContentViewResolverInterface $contentViewResolver,
        private readonly ContentMergerInterface $contentMerger,
    ) {
        /** @var EntityRepository<ArchiveDimensionContent> $repository */
        $repository = $this->entityManager->getRepository(ArchiveDimensionContent::class);
        $this->archiveDimensionContentRepository = $repository;
    }

    public static function getResourceKey(): string
    {
        return Archive::RESOURCE_KEY;
    }

    public function refresh(?array $filter = null): \Generator
    {
        $archiveDimensionContentsGenerator = $this->getArchiveDimensionContentsGenerator($filter);

        $currentResourceId = null;
        $currentGroup = [];

        /** @var ArchiveDimensionContent $dimensionContent */
        foreach ($archiveDimensionContentsGenerator as $dimensionContent) {
            $resourceId = $dimensionContent->getResource()->getId();

            if (null === $currentResourceId) {
                $currentResourceId = $resourceId;
            }

            if ($resourceId !== $currentResourceId) {
                foreach ($this->resolveArchiveDimensionContents($currentGroup) as $merged) {
                    $this->processArchiveDimensionContent($merged);
                    yield $merged;
                }

                $currentGroup = [];
                $currentResourceId = $resourceId;
            }

            $currentGroup[] = $dimensionContent;
        }

        if ([] !== $currentGroup) {
            foreach ($this->resolveArchiveDimensionContents($currentGroup) as $merged) {
                $this->processArchiveDimensionContent($merged);
                yield $merged;
            }
        }
    }

    private function processArchiveDimensionContent(ArchiveDimensionContent $archiveDimensionContent): void
    {
        $referenceCollector = new ReferenceCollector(
            referenceRepository: $this->referenceRepository,
            referenceResourceKey: $archiveDimensionContent->getResourceKey(),
            referenceResourceId: (string) $archiveDimensionContent->getResource()->getId(),
            referenceLocale: $archiveDimensionContent->getLocale() ?? '',
            referenceTitle: $archiveDimensionContent->getTitle() ?? '',
            referenceContext: $archiveDimensionContent->getStage(),
            referenceRouterAttributes: [
                'locale' => $archiveDimensionContent->getLocale() ?? '',
            ]
        );

        $contentViews = $this->contentViewResolver->getContentViews(dimensionContent: $archiveDimensionContent);

        foreach ($contentViews as $key => $contentView) {
            $basePath = 'template' !== $key ? (string) $key : '';
            $references = $contentView->getAllReferencesRecursively($basePath);

            foreach ($references as $reference) {
                $referenceCollector->addReference(
                    $reference->getResourceKey(),
                    (string) $reference->getResourceId(),
                    $reference->getPath()
                );
            }
        }

        $this->collectDirectReferences($archiveDimensionContent, $referenceCollector);

        $referenceCollector->persistReferences();
    }

    private function collectDirectReferences(
        ArchiveDimensionContent $archiveDimensionContent,
        ReferenceCollector $referenceCollector
    ): void {
        $image = $archiveDimensionContent->getImage();
        if (null !== $image) {
            $referenceCollector->addReference(
                'media',
                (string) $image->getId(),
                'image'
            );
        }

        $document = $archiveDimensionContent->getDocument();
        if (null !== $document) {
            $referenceCollector->addReference(
                'media',
                (string) $document->getId(),
                'document'
            );
        }

        $images = $archiveDimensionContent->getImages();
        if (null !== $images && \is_array($images)) {
            foreach ($images as $index => $imageData) {
                if (isset($imageData['id'])) {
                    $referenceCollector->addReference(
                        'media',
                        (string) $imageData['id'],
                        'images[' . $index . ']'
                    );
                }
            }
        }

        $author = $archiveDimensionContent->getAuthor();
        if (null !== $author) {
            $referenceCollector->addReference(
                'contacts',
                (string) $author->getId(),
                'author'
            );
        }
    }

    /**
     * @param array{
     *      resourceId: string,
     *      resourceKey: string,
     *      locale: string,
     *      stage: string
     *  }|null $filter
     *
     * @return iterable<ArchiveDimensionContent>
     */
    private function getArchiveDimensionContentsGenerator(?array $filter = null): iterable
    {
        $queryBuilder = $this->archiveDimensionContentRepository->createQueryBuilder('dimensionContent')
            ->where('dimensionContent.version = :version')
            ->setParameter('version', DimensionContentInterface::CURRENT_VERSION)
            ->orderBy('dimensionContent.archive', 'ASC');

        if (null !== $filter) {
            $queryBuilder
                ->join(
                    'dimensionContent.archive',
                    'archive',
                    Join::WITH,
                    'archive.uuid = :resourceId'
                )
                ->andWhere('dimensionContent.locale = :locale OR dimensionContent.locale IS NULL')
                ->andWhere('dimensionContent.stage = :stage')
                ->setParameter('resourceId', $filter['resourceId'])
                ->setParameter('locale', $filter['locale'])
                ->setParameter('stage', $filter['stage']);
        }

        /** @var iterable<ArchiveDimensionContent> $result */
        $result = $queryBuilder->getQuery()->toIterable();

        return $result;
    }

    /**
     * @param iterable<ArchiveDimensionContent> $archiveDimensionContents
     *
     * @return \Generator<ArchiveDimensionContent>
     */
    private function resolveArchiveDimensionContents(iterable $archiveDimensionContents): \Generator
    {
        $groupedArchiveDimensionContents = [];

        /** @var ArchiveDimensionContent $archiveDimensionContent */
        foreach ($archiveDimensionContents as $archiveDimensionContent) {
            $resourceId = $archiveDimensionContent->getResource()->getId();
            $stage = $archiveDimensionContent->getStage();
            $locale = $archiveDimensionContent->getLocale() ?? '';

            $groupedArchiveDimensionContents[$resourceId][$stage][$locale] = $archiveDimensionContent;
        }

        foreach ($groupedArchiveDimensionContents as $archiveDimensionContentByStage) {
            foreach ($archiveDimensionContentByStage as $stage => $archiveDimensionContentByLocale) {
                $unlocalizedDimensionContent = $archiveDimensionContentByLocale[''] ?? null;

                foreach ($archiveDimensionContentByLocale as $locale => $localizedDimensionContent) {
                    if ('' === $locale) {
                        continue;
                    }

                    if (null !== $unlocalizedDimensionContent) {
                        $dimensionContentCollection = new DimensionContentCollection(
                            new ArrayCollection([$unlocalizedDimensionContent, $localizedDimensionContent]),
                            [
                                'locale' => $locale,
                                'stage' => $stage,
                            ],
                            ArchiveDimensionContent::class
                        );

                        /** @var ArchiveDimensionContent $mergedDimensionContent */
                        $mergedDimensionContent = $this->contentMerger->merge($dimensionContentCollection);

                        if (null === $mergedDimensionContent->getLocale()) {
                            $mergedDimensionContent->setLocale($locale);
                        }

                        if (empty($mergedDimensionContent->getTemplateKey())) {
                            $mergedDimensionContent->setTemplateKey('archive');
                        }

                        yield $mergedDimensionContent;
                    } else {
                        yield $localizedDimensionContent;
                    }
                }
            }
        }
    }
}
