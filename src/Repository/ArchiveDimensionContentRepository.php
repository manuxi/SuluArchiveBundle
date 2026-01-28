<?php

declare(strict_types=1);

namespace Manuxi\SuluArchiveBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Manuxi\SuluArchiveBundle\Entity\ArchiveDimensionContent;

/**
 * @extends ServiceEntityRepository<ArchiveDimensionContent>
 */
class ArchiveDimensionContentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ArchiveDimensionContent::class);
    }

    public function findMissingLocaleByIds(array $ids, string $locale, int $localesCount = 0): array
    {
        if (empty($ids)) {
            return [];
        }

        $qb = $this->createQueryBuilder('dimensionContent');
        $qb->select('identity(dimensionContent.archive) as archive')
            ->where($qb->expr()->in('dimensionContent.archive', $ids))
            ->andWhere('dimensionContent.locale = :locale')
            ->setParameter('locale', $locale);

        return $qb->getQuery()->getArrayResult();
    }
}
