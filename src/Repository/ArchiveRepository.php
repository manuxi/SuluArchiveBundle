<?php

declare(strict_types=1);

namespace Manuxi\SuluArchiveBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Manuxi\SuluArchiveBundle\Entity\Archive;
use Manuxi\SuluArchiveBundle\Entity\ArchiveDimensionContent;
use Sulu\Content\Domain\Model\DimensionContentInterface;
use Sulu\Content\Domain\Model\WorkflowInterface;
use Sulu\Content\Infrastructure\Doctrine\DimensionContentQueryEnhancer;

class ArchiveRepository extends ServiceEntityRepository
{
    public const GROUP_SELECT_ARCHIVE_ADMIN = 'archive_admin';
    public const GROUP_SELECT_ARCHIVE_WEBSITE = 'archive_website';
    public const SELECT_ARCHIVE_CONTENT = 'with-archive-content';

    private const SELECTS = [
        self::GROUP_SELECT_ARCHIVE_ADMIN => [
            self::SELECT_ARCHIVE_CONTENT => [
                DimensionContentQueryEnhancer::GROUP_SELECT_CONTENT_ADMIN => true,
            ],
        ],
        self::GROUP_SELECT_ARCHIVE_WEBSITE => [
            self::SELECT_ARCHIVE_CONTENT => [
                DimensionContentQueryEnhancer::GROUP_SELECT_CONTENT_WEBSITE => true,
            ],
        ],
    ];

    public function __construct(
        ManagerRegistry $registry,
        private DimensionContentQueryEnhancer $dimensionContentQueryEnhancer,
    ) {
        parent::__construct($registry, Archive::class);
    }

    public function findAll(): array
    {
        $queryBuilder = $this->createQueryBuilder('archive')
            ->leftJoin('archive.dimensionContents', 'dimensionContent')
            ->addSelect('dimensionContent');

        return $queryBuilder->getQuery()->getResult();
    }

    public function findByUuid(string $uuid): ?Archive
    {
        $queryBuilder = $this->createQueryBuilder('archive')
            ->leftJoin('archive.dimensionContents', 'dimensionContent')
            ->addSelect('dimensionContent')
            ->where('archive.uuid = :uuid')
            ->setParameter('uuid', $uuid);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * @param string[] $uuids
     * @return Archive[]
     */
    public function findByUuids(array $uuids, string $locale, string $stage = DimensionContentInterface::STAGE_LIVE): array
    {
        $filters = ['uuids' => $uuids, 'locale' => $locale, 'stage' => $stage];

        $qb = $this->buildQueryBuilder(
            $filters,
            [],
            [self::GROUP_SELECT_ARCHIVE_WEBSITE => true]
        );

        return $qb->getQuery()->getResult();
    }

    public function findAllByLocale(string $locale, string $stage = DimensionContentInterface::STAGE_LIVE): array
    {
        $qb = $this->buildQueryBuilder(
            ['locale' => $locale, 'stage' => $stage],
            [],
            [self::GROUP_SELECT_ARCHIVE_WEBSITE => true]
        );

        return $qb->getQuery()->getResult();
    }

    /**
     * @param array{
     *     uuid?: string,
     *     uuids?: string[],
     *     locale?: string|null,
     *     stage?: string|null,
     *     categoryIds?: int[],
     *     categoryKeys?: string[],
     *     categoryOperator?: 'AND'|'OR',
     *     tagIds?: int[],
     *     tagNames?: string[],
     *     tagOperator?: 'AND'|'OR',
     *     templateKeys?: string[],
     *     types?: string[],
     * } $filters
     * @param array<string, string> $sortBys
     * @param array<string, mixed> $selects
     *
     * @return Archive[]
     */
    public function findByFilters(array $filters = [], array $sortBys = [], array $selects = []): array
    {
        $filters = $this->normalizeFindByFilters($filters);
        $selects = $this->normalizeSelects($selects);

        $queryBuilder = $this->buildQueryBuilder($filters, $sortBys, $selects);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @param array{
     *     uuid?: string,
     *     uuids?: string[],
     *     locale?: string|null,
     *     stage?: string|null,
     *     categoryIds?: int[],
     *     categoryKeys?: string[],
     *     categoryOperator?: 'AND'|'OR',
     *     tagIds?: int[],
     *     tagNames?: string[],
     *     tagOperator?: 'AND'|'OR',
     *     templateKeys?: string[],
     *     types?: string[],
     * } $filters
     */
    public function countBy(array $filters = []): int
    {
        $filters = $this->normalizeFindByFilters($filters);
        $selects = $this->normalizeSelects([]);
        $queryBuilder = $this->buildQueryBuilder($filters, [], $selects);

        $queryBuilder->select('COUNT(DISTINCT archive.uuid)');

        return (int) $queryBuilder->getQuery()->getSingleScalarResult();
    }

    public function countAll(): int
    {
        return (int) $this->createQueryBuilder('a')
            ->select('COUNT(a.uuid)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countPublished(string $locale): int
    {
        $qb = $this->createQueryBuilder('archive');

        $qb->select('COUNT(DISTINCT archive.uuid)')
            ->leftJoin('archive.dimensionContents', 'dc')
            ->where('dc.locale = :locale')
            ->andWhere('dc.stage = :stage')
            ->andWhere('dc.workflowPlace = :published')
            ->setParameter('locale', $locale)
            ->setParameter('stage', DimensionContentInterface::STAGE_LIVE)
            ->setParameter('published', WorkflowInterface::WORKFLOW_PLACE_PUBLISHED);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    public function add(Archive $archive): void
    {
        $this->getEntityManager()->persist($archive);
    }

    public function remove(Archive $archive): void
    {
        $this->getEntityManager()->remove($archive);
    }

    /**
     * @param array<string, mixed> $filters
     * @param array<string, string> $sortBys
     * @param array<string, mixed> $selects
     */
    private function buildQueryBuilder(
        array $filters = [],
        array $sortBys = [],
        array $selects = []
    ): QueryBuilder {
        $queryBuilder = $this->createQueryBuilder('archive');

        $this->applyContentJoin($queryBuilder, $filters, $sortBys, $selects);
        $this->applyFilters($queryBuilder, $filters);
        $this->applySortBys($queryBuilder, $sortBys);
        $this->applyPagination($queryBuilder, $filters);

        return $queryBuilder;
    }

    /**
     * @param array<string, mixed> $filters
     *
     * @return array<string, mixed>
     */
    private function normalizeFindByFilters(array $filters): array
    {
        $filters['stage'] = $filters['stage'] ?? DimensionContentInterface::STAGE_DRAFT;

        return $filters;
    }

    /**
     * @param array<string, mixed> $selects
     *
     * @return array<string, mixed>
     */
    private function normalizeSelects(array $selects): array
    {
        $normalizedSelects = [];

        foreach (self::SELECTS as $groupKey => $groupSelects) {
            if (true === ($selects[$groupKey] ?? false)) {
                foreach ($groupSelects as $selectKey => $selectValue) {
                    $normalizedSelects[$selectKey] = $selectValue;
                }
            }
        }

        foreach ($selects as $key => $value) {
            if (\is_string($key) && \is_array($value)) {
                $normalizedSelects[$key] = $value;
            }
        }

        return $normalizedSelects;
    }

    /**
     * @param array<string, mixed> $filters
     * @param array<string, string> $sortBys
     * @param array<string, mixed> $selects
     */
    private function applyContentJoin(
        QueryBuilder $queryBuilder,
        array $filters,
        array $sortBys,
        array $selects
    ): void {
        $locale = $filters['locale'] ?? null;
        $stage = $filters['stage'] ?? DimensionContentInterface::STAGE_DRAFT;

        $queryBuilder->leftJoin('archive.dimensionContents', 'dimensionContent');

        $normalizedSelects = $this->normalizeSelects($selects);
        if (!empty($normalizedSelects)) {
            $this->dimensionContentQueryEnhancer->addSelects(
                $queryBuilder,
                ArchiveDimensionContent::class,
                ['locale' => $locale, 'stage' => $stage],
                $normalizedSelects
            );
        } else {
            $queryBuilder->addSelect('dimensionContent');
        }
    }

    /**
     * @param array<string, mixed> $filters
     */
    private function applyFilters(QueryBuilder $queryBuilder, array $filters): void
    {
        if (isset($filters['uuid'])) {
            $queryBuilder->andWhere('archive.uuid = :uuid');
            $queryBuilder->setParameter('uuid', $filters['uuid']);
        }

        if (isset($filters['uuids'])) {
            $queryBuilder->andWhere('archive.uuid IN (:uuids)');
            $queryBuilder->setParameter('uuids', $filters['uuids']);
        }

        if (isset($filters['types'])) {
            $queryBuilder->andWhere('dimensionContent.type IN (:types)');
            $queryBuilder->setParameter('types', $filters['types']);
        }
    }

    /**
     * @param array{
     *     uuid?: 'asc'|'desc',
     *     title?: 'asc'|'desc',
     *     created?: 'asc'|'desc',
     *     changed?: 'asc'|'desc',
     * } $sortBys
     */
    private function applySortBys(QueryBuilder $queryBuilder, array $sortBys): void
    {
        foreach ($sortBys as $field => $direction) {
            switch ($field) {
                case 'uuid':
                    $queryBuilder->addOrderBy('archive.uuid', $direction);
                    break;
                case 'title':
                    $queryBuilder->addOrderBy('dimensionContent.title', $direction);
                    break;
                case 'created':
                    $queryBuilder->addOrderBy('dimensionContent.created', $direction);
                    break;
                case 'changed':
                    $queryBuilder->addOrderBy('dimensionContent.changed', $direction);
                    break;
            }
        }
    }

    /**
     * @param array<string, mixed> $filters
     */
    private function applyPagination(QueryBuilder $queryBuilder, array $filters): void
    {
        if (isset($filters['limit'])) {
            $queryBuilder->setMaxResults($filters['limit']);
        }

        if (isset($filters['offset'])) {
            $queryBuilder->setFirstResult($filters['offset']);
        }
    }
}
