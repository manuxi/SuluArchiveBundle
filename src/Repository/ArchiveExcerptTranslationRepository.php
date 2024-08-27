<?php

declare(strict_types=1);

namespace Manuxi\SuluArchiveBundle\Repository;

use Manuxi\SuluArchiveBundle\Entity\ArchiveExcerptTranslation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ArchiveExcerptTranslation|null find($id, $lockMode = null, $lockVersion = null)
 * @method ArchiveExcerptTranslation|null findOneBy(array $criteria, array $orderBy = null)
 * @method ArchiveExcerptTranslation[]    findAll()
 * @method ArchiveExcerptTranslation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @extends ServiceEntityRepository<ArchiveTranslation>
 */
class ArchiveExcerptTranslationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ArchiveExcerptTranslation::class);
    }
}
