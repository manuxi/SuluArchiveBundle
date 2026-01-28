<?php

declare(strict_types=1);

namespace Manuxi\SuluArchiveBundle\Content;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Manuxi\SuluArchiveBundle\Admin\ArchiveAdmin;
use Manuxi\SuluArchiveBundle\Entity\Archive;
use Manuxi\SuluArchiveBundle\Entity\ArchiveDimensionContent;
use Manuxi\SuluArchiveBundle\Repository\ArchiveRepository;
use Sulu\Bundle\AdminBundle\SmartContent\Configuration\Builder;
use Sulu\Bundle\AdminBundle\SmartContent\Configuration\BuilderInterface;
use Sulu\Bundle\AdminBundle\SmartContent\Configuration\ProviderConfigurationInterface;
use Sulu\Bundle\AdminBundle\SmartContent\SmartContentProviderInterface;
use Sulu\Bundle\AdminBundle\SmartContent\SmartContentQueryEnhancer;
use Sulu\Content\Domain\Model\DimensionContentInterface;
use Sulu\Content\Infrastructure\Doctrine\DimensionContentQueryEnhancer;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @phpstan-type ArchiveSmartContentFilters array{
 *     categories: int[],
 *     categoryOperator: 'AND'|'OR',
 *     websiteCategories: string[],
 *     websiteCategoryOperator: 'AND'|'OR',
 *     tags: string[],
 *     tagOperator: 'AND'|'OR',
 *     websiteTags: string[],
 *     websiteTagOperator: 'AND'|'OR',
 *     types: string[],
 *     typesOperator: 'OR',
 *     locale: string,
 *     dataSource: string|null,
 *     limit: int|null,
 *     includeSubFolders: bool,
 *     excludeDuplicates: bool,
 *     offset?: int,
 *     stage?: string,
 * }
 */
class ArchiveSmartContentProvider implements SmartContentProviderInterface
{
    /**
     * @var class-string<ArchiveDimensionContent>
     */
    private string $archiveDimensionContentClassName;

    private ?ArchiveRepository $archiveRepository = null;

    /**
     * @param array<string, array{name: string, color: string}> $archiveTypes
     */
    public function __construct(
        private DimensionContentQueryEnhancer $dimensionContentQueryEnhancer,
        private SmartContentQueryEnhancer $smartContentQueryEnhancer,
        private EntityManagerInterface $entityManager,
        private TranslatorInterface $translator,
        private array $archiveTypes = [],
    ) {
        $entityDimensionContentRepository = $entityManager->getRepository(ArchiveDimensionContent::class);
        $this->archiveDimensionContentClassName = $entityDimensionContentRepository->getClassName();
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

    public function getConfiguration(): ProviderConfigurationInterface
    {
        return $this->getConfigurationBuilder()->getConfiguration();
    }

    protected function getConfigurationBuilder(): BuilderInterface
    {
        return Builder::create()
            ->enableTags()
            ->enableCategories()
            ->enableLimit()
            ->enablePagination()
            ->enablePresentAs()
            ->enableSorting($this->getSorting())
            ->enableTypes($this->getTypes())
            ->enableView(ArchiveAdmin::EDIT_TABS_VIEW, ['id' => 'id']);
    }

    protected function getTypes(): array
    {
        $types = [];

        foreach ($this->archiveTypes as $key => $config) {
            $types[] = [
                'type' => $key,
                'title' => $this->translator->trans($config['name'], [], 'admin'),
            ];
        }

        return $types;
    }

    protected function getSorting(): array
    {
        return [
            ['column' => 'title', 'title' => $this->translator->trans('sulu_archive.title', [], 'admin')],
            ['column' => 'authored', 'title' => $this->translator->trans('sulu_archive.authored', [], 'admin')],
            ['column' => 'workflowPublished', 'title' => $this->translator->trans('sulu_admin.published', [], 'admin')],
            ['column' => 'created', 'title' => $this->translator->trans('sulu_admin.created', [], 'admin')],
            ['column' => 'changed', 'title' => $this->translator->trans('sulu_admin.changed', [], 'admin')],
        ];
    }

    public function countBy(array $filters, array $params = []): int
    {
        $filters = $this->enhanceWithDimensionAttributes($filters);

        $alias = 'archive';
        $queryBuilder = $this->getArchiveRepository()->createQueryBuilder($alias);

        $filters = $this->mapFilters($filters);
        $this->dimensionContentQueryEnhancer->addFilters(
            $queryBuilder,
            $alias,
            $this->archiveDimensionContentClassName,
            $filters,
            [],
        );
        $this->addInternalFilters($queryBuilder, $filters, $alias);

        $queryBuilder->select('COUNT(DISTINCT ' . $alias . '.id)');

        return (int) $queryBuilder->getQuery()->getSingleScalarResult();
    }

    /**
     * @return array<array{id: string, title: string}>
     */
    public function findFlatBy(array $filters, array $sortBys, array $params = []): array
    {
        $filters = $this->enhanceWithDimensionAttributes($filters);

        $alias = 'archive';
        $queryBuilder = $this->getArchiveRepository()->createQueryBuilder($alias);

        $filters = $this->mapFilters($filters);
        $this->dimensionContentQueryEnhancer->addFilters(
            $queryBuilder,
            $alias,
            $this->archiveDimensionContentClassName,
            $filters,
            $sortBys,
        );
        $dimensionContentAlias = $this->addInternalFilters($queryBuilder, $filters, $alias);

        $queryBuilder->select('DISTINCT ' . $alias . '.id as id');
        $queryBuilder->addSelect($dimensionContentAlias . '.title');
        $queryBuilder->addSelect($dimensionContentAlias . '.workflowPlace');
        $queryBuilder->addSelect($dimensionContentAlias . '.workflowPublished');
        $queryBuilder->addSelect($dimensionContentAlias . '.type');

        $this->smartContentQueryEnhancer->addOrderBySelects($queryBuilder);
        $limit = isset($filters['limit']) ? (int) $filters['limit'] : null;
        $offset = isset($filters['offset']) ? (int) $filters['offset'] : 0;
        $this->smartContentQueryEnhancer->addPagination($queryBuilder, $offset, $limit);

        /** @var array{id: int|string, title?: string}[] $queryResult */
        $queryResult = $queryBuilder->getQuery()->getArrayResult();

        /** @var array{id: string, title: string}[] $result */
        $result = \array_map(
            function (array $item) {
                return [
                    'id' => (string) $item['id'],
                    'title' => (string) ($item['title'] ?? ''),
                    'publishedState' => 'published' === ($item['workflowPlace'] ?? ''),
                    'published' => $item['workflowPublished'] ?? null,
                ];
            },
            $queryResult
        );

        return $result;
    }

    protected function enhanceWithDimensionAttributes(array $filters): array
    {
        $dimensionAttributes = [
            'stage' => $filters['stage'] ?? DimensionContentInterface::STAGE_LIVE,
        ];

        return \array_merge($dimensionAttributes, $filters);
    }

    protected function mapFilters(array $filters): array
    {
        $mappedFilters = [
            'categoryIds' => $filters['categories'] ?? [],
            'categoryOperator' => $filters['categoryOperator'] ?? 'OR',
            'websiteCategories' => $filters['websiteCategories'] ?? [],
            'websiteCategoryOperator' => $filters['websiteCategoryOperator'] ?? 'OR',
            'tagNames' => $filters['tags'] ?? [],
            'tagOperator' => $filters['tagOperator'] ?? 'OR',
            'websiteTags' => $filters['websiteTags'] ?? [],
            'websiteTagOperator' => $filters['websiteTagOperator'] ?? 'OR',
            'templateKeys' => [],
            'customTypes' => $filters['types'] ?? [],
            'typesOperator' => $filters['typesOperator'] ?? 'OR',
            'locale' => $filters['locale'],
            'dataSource' => $filters['dataSource'] ?? null,
            'limit' => $filters['limit'] ?? null,
            'includeSubFolders' => $filters['includeSubFolders'] ?? false,
            'excludeDuplicates' => $filters['excludeDuplicates'] ?? false,
        ];

        if (isset($filters['offset'])) {
            $mappedFilters['offset'] = $filters['offset'];
        }

        if (isset($filters['stage'])) {
            $mappedFilters['stage'] = $filters['stage'];
        }

        return $mappedFilters;
    }

    protected function addInternalFilters(QueryBuilder $queryBuilder, array $filters, string $alias): string
    {
        $dimensionContentAlias = null;
        $joins = $queryBuilder->getDQLPart('join');

        if (isset($joins[$alias])) {
            foreach ($joins[$alias] as $join) {
                if ($join->getJoin() === $alias . '.dimensionContents') {
                    $dimensionContentAlias = $join->getAlias();
                    break;
                }
            }
        }

        if (!$dimensionContentAlias) {
            $dimensionContentAlias = 'dimensionContent';
            $stage = $filters['stage'] ?? DimensionContentInterface::STAGE_LIVE;
            $locale = $filters['locale'];

            $queryBuilder->innerJoin(
                $alias . '.dimensionContents',
                $dimensionContentAlias,
                'WITH',
                $dimensionContentAlias . '.locale = :locale AND ' . $dimensionContentAlias . '.stage = :stage'
            );
            $queryBuilder->setParameter('locale', $locale);
            $queryBuilder->setParameter('stage', $stage);
        }

        $this->addTypeFilters($queryBuilder, $filters['customTypes'] ?? [], $dimensionContentAlias);

        return $dimensionContentAlias;
    }

    /**
     * @param string[] $types
     */
    protected function addTypeFilters(QueryBuilder $queryBuilder, array $types, string $alias): void
    {
        if (empty($types)) {
            return;
        }

        $configurableTypes = \array_intersect($types, \array_keys($this->archiveTypes));

        if (!empty($configurableTypes)) {
            $queryBuilder->andWhere($alias . '.type IN (:archiveTypes)')
                ->setParameter('archiveTypes', $configurableTypes);
        }
    }

    public function getType(): string
    {
        return Archive::RESOURCE_KEY;
    }

    public function getResourceLoaderKey(): string
    {
        return Archive::RESOURCE_KEY;
    }
}
