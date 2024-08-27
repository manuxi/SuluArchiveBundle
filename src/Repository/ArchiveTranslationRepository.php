<?php

declare(strict_types=1);

namespace Manuxi\SuluArchiveBundle\Repository;

use Doctrine\Common\Collections\Criteria;
use Manuxi\SuluArchiveBundle\Entity\Archive;
use Manuxi\SuluArchiveBundle\Entity\ArchiveTranslation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ArchiveTranslation|null find($id, $lockMode = null, $lockVersion = null)
 * @method ArchiveTranslation|null findOneBy(array $criteria, array $orderBy = null)
 * @method ArchiveTranslation[]    findAll()
 * @method ArchiveTranslation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @extends ServiceEntityRepository<ArchiveTranslation>
 */
class ArchiveTranslationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ArchiveTranslation::class);
    }

    public function findMissingLocaleByIds(array $ids, string $missingLocale, int $countLocales)
    {
        $query = $this->createQueryBuilder('et')
            ->addCriteria($this->createIdsInCriteria($ids))
            ->groupby('et.archive')
            ->having('archiveCount < :countLocales')
            ->setParameter('countLocales', $countLocales)
            ->andHaving('et.locale = :locale')
            ->setParameter('locale', $missingLocale)
            ->select('IDENTITY(et.archive) as archive, et.locale, count(et.archive) as archiveCount')
            ->getQuery()
        ;

        return $query->getResult();
    }

    private function createIdsInCriteria(array $ids): Criteria
    {
        return Criteria::create()
            ->andWhere(Criteria::expr()->in('archive', $ids))
            ;
    }

}
