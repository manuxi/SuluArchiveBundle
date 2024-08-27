<?php

declare(strict_types=1);

namespace Manuxi\SuluArchiveBundle\Repository;

use Manuxi\SuluArchiveBundle\Entity\ArchiveSeoTranslation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ArchiveSeoTranslation|null find($id, $lockMode = null, $lockVersion = null)
 * @method ArchiveSeoTranslation|null findOneBy(array $criteria, array $orderBy = null)
 * @method ArchiveSeoTranslation[]    findAll()
 * @method ArchiveSeoTranslation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @extends ServiceEntityRepository<ArchiveTranslation>
 */
class ArchiveSeoTranslationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ArchiveSeoTranslation::class);
    }
}
