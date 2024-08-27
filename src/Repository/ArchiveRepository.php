<?php

declare(strict_types=1);

namespace Manuxi\SuluArchiveBundle\Repository;

use Manuxi\SuluArchiveBundle\Entity\Archive;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Sulu\Component\SmartContent\Orm\DataProviderRepositoryInterface;
use Sulu\Component\SmartContent\Orm\DataProviderRepositoryTrait;

/**
 * @method Archive|null find($id, $lockMode = null, $lockVersion = null)
 * @method Archive|null findOneBy(array $criteria, array $orderBy = null)
 * @method Archive[]    findAll()
 * @method Archive[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @extends ServiceEntityRepository<Archive>
 */
class ArchiveRepository extends ServiceEntityRepository implements DataProviderRepositoryInterface
{
    use DataProviderRepositoryTrait {
        findByFilters as protected parentFindByFilters;
    }

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Archive::class);
    }

    public function create(string $locale): Archive
    {
        $entity = new Archive();
        $entity->setLocale($locale);
        $entity->setPublished(false);

        return $entity;
    }

    public function remove(int $id): void
    {
        /** @var object $entity */
        $entity = $this->getEntityManager()->getReference(
            $this->getClassName(),
            $id
        );

        $this->getEntityManager()->remove($entity);
        $this->getEntityManager()->flush();
    }

    public function save(Archive $entity): Archive
    {
        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();
        return $entity;
    }

    public function publish(Archive $entity): Archive
    {
        $entity->setPublished(true);
        return $this->save($entity);
    }

    public function unpublish(Archive $entity): Archive
    {
        $entity->setPublished(false);
        return $this->save($entity);
    }

    public function findById(int $id, string $locale): ?Archive
    {
        $entity = $this->find($id);

        if (!$entity) {
            return null;
        }

        $entity->setLocale($locale);

        return $entity;
    }

    public function findAllForSitemap(string $locale, int $limit = null, int $offset = null): array
    {
        $queryBuilder = $this->createQueryBuilder('archive')
            ->leftJoin('archive.translations', 'translation')
            ->where('translation.published = 1')
            ->andWhere('translation.locale = :locale')->setParameter('locale', $locale)
            ->orderBy('translation.authored', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset);

        $this->prepareFilter($queryBuilder, []);

        $archive = $queryBuilder->getQuery()->getResult();
        if (!$archive) {
            return [];
        }
        return $archive;
    }

    public function countForSitemap(string $locale)
    {
        $query = $this->createQueryBuilder('archive')
            ->select('count(archive)')
            ->leftJoin('archive.translations', 'translation')
            ->andWhere('translation.locale = :locale')->setParameter('locale', $locale);
        return $query->getQuery()->getSingleScalarResult();
    }

    protected function appendJoins(QueryBuilder $queryBuilder, $alias, $locale): void
    {

    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param string $alias
     * @param string $locale
     * @param mixed[] $options
     *
     * @return string[]
     */
    protected function append(QueryBuilder $queryBuilder, string $alias, string $locale, $options = []): array
    {
        //$queryBuilder->andWhere($alias . '.published = true');

        return [];
    }

    public function appendCategoriesRelation(QueryBuilder $queryBuilder, $alias): string
    {
        return $alias . '.category';
        //$queryBuilder->addSelect($alias.'.category');
    }

    protected function appendSortByJoins(QueryBuilder $queryBuilder, string $alias, string $locale): void
    {
        $queryBuilder->innerJoin($alias . '.translations', 'translation', Join::WITH, 'translation.locale = :locale');
        $queryBuilder->setParameter('locale', $locale);
    }

    public function findByFilters($filters, $page, $pageSize, $limit, $locale, $options = []): array
    {
        $entities = $this->getPublishedArchive($filters, $locale, $page, $pageSize, $limit, $options);

        return \array_map(
            function (Archive $entity) use ($locale) {
                return $entity->setLocale($locale);
            },
            $entities
        );
    }

    public function hasNextPage(array $filters, ?int $page, ?int $pageSize, ?int $limit, string $locale, array $options = []): bool
    {
        $pageCurrent = (key_exists('page', $options)) ? (int)$options['page'] : 0;
        $archiveCount = $this->createQueryBuilder('archive')
            ->select('count(archive.id)')
            ->leftJoin('archive.translations', 'translation')
            ->where('translation.published = 1')
            ->andWhere('translation.locale = :locale')
            ->setParameter('locale', $locale)
            ->getQuery()
            ->getSingleScalarResult();

        if ((int)($limit * $pageCurrent) + $limit < (int)$archiveCount) {
            return true;
        }

        return false;
    }

    public function getPublishedArchive(array $filters, string $locale, ?int $page, $pageSize, $limit = null, array $options): array
    {
        $pageCurrent = (key_exists('page', $options)) ? (int)$options['page'] : 0;

        $queryBuilder = $this->createQueryBuilder('archive')
            ->leftJoin('archive.translations', 'translation')
            ->where('translation.published = 1')
            ->andWhere('translation.locale = :locale')->setParameter('locale', $locale)
            ->orderBy('translation.authored', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($pageCurrent * $limit);

        $this->prepareFilter($queryBuilder, $filters);

        $archive = $queryBuilder->getQuery()->getResult();
        if (!$archive) {
            return [];
        }
        return $archive;
    }

    private function prepareFilter(QueryBuilder $queryBuilder, array $filters): void
    {
        if (isset($filters['sortBy'])) {
            $queryBuilder->orderBy($filters['sortBy'], $filters['sortMethod']);
        }

        if (!empty($filters['tags']) || !empty($filters['categories'])) {
            $queryBuilder->leftJoin('archive.archiveExcerpt', 'excerpt')
                ->leftJoin('excerpt.translations', 'excerpt_translation');
        }
        $this->prepareTypesFilter($queryBuilder, $filters);
        $this->prepareTagsFilter($queryBuilder, $filters);
        $this->prepareCategoriesFilter($queryBuilder, $filters);
    }

    private function prepareTagsFilter(QueryBuilder $queryBuilder, array $filters): void
    {
        if (!empty($filters['tags'])) {

            $queryBuilder->leftJoin('excerpt_translation.tags', 'tags');

            $i = 0;
            if ($filters['tagOperator'] === "and") {
                $andWhere = "";
                foreach ($filters['tags'] as $tag) {
                    if ($i === 0) {
                        $andWhere .= "tags = :tag" . $i;
                    } else {
                        $andWhere .= " AND tags = :tag" . $i;
                    }
                    $queryBuilder->setParameter("tag" . $i, $tag);
                    $i++;
                }
                $queryBuilder->andWhere($andWhere);
            } else if ($filters['tagOperator'] === "or") {
                $orWhere = "";
                foreach ($filters['tags'] as $tag) {
                    if ($i === 0) {
                        $orWhere .= "tags = :tag" . $i;
                    } else {
                        $orWhere .= " OR tags = :tag" . $i;
                    }
                    $queryBuilder->setParameter("tag" . $i, $tag);
                    $i++;
                }
                $queryBuilder->andWhere($orWhere);
            }
        }
    }

    private function prepareCategoriesFilter(QueryBuilder $queryBuilder, array $filters): void
    {
        if (!empty($filters['categories'])) {

            $queryBuilder->leftJoin('excerpt_translation.categories', 'categories');

            $i = 0;
            if ($filters['categoryOperator'] === "and") {
                $andWhere = "";
                foreach ($filters['categories'] as $category) {
                    if ($i === 0) {
                        $andWhere .= "categories = :category" . $i;
                    } else {
                        $andWhere .= " AND categories = :category" . $i;
                    }
                    $queryBuilder->setParameter("category" . $i, $category);
                    $i++;
                }
                $queryBuilder->andWhere($andWhere);
            } else if ($filters['categoryOperator'] === "or") {
                $orWhere = "";
                foreach ($filters['categories'] as $category) {
                    if ($i === 0) {
                        $orWhere .= "categories = :category" . $i;
                    } else {
                        $orWhere .= " OR categories = :category" . $i;
                    }
                    $queryBuilder->setParameter("category" . $i, $category);
                    $i++;
                }
                $queryBuilder->andWhere($orWhere);
            }
        }
    }

    private function prepareTypesFilter(QueryBuilder $queryBuilder, array $filters): void
    {
        if(!empty($filters['types'])) {
            $orWhere = '';
            for ($i = 0; $i < count($filters['types']); $i++) {
                if ($i === 0) {
                    $orWhere .= "archive.type = :type" . $i;
                } else {
                    $orWhere .= " OR archive.type = :type" . $i;
                }
                $queryBuilder->setParameter("type" . $i, $filters['types'][$i]);
                $i++;
            }
            $queryBuilder->andWhere($orWhere);
        }
    }

}
