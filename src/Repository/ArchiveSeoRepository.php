<?php

declare(strict_types=1);

namespace Manuxi\SuluArchiveBundle\Repository;

use Manuxi\SuluArchiveBundle\Entity\ArchiveSeo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ArchiveSeo|null find($id, $lockMode = null, $lockVersion = null)
 * @method ArchiveSeo|null findOneBy(array $criteria, array $orderBy = null)
 * @method ArchiveSeo[]    findAll()
 * @method ArchiveSeo[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @extends ServiceEntityRepository<Archive>
 */
class ArchiveSeoRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ArchiveSeo::class);
    }

    public function create(string $locale): ArchiveSeo
    {
        $archiveSeo = new ArchiveSeo();
        $archiveSeo->setLocale($locale);

        return $archiveSeo;
    }

    /**
     * @param int $id
     * @throws \Doctrine\ORM\Exception\ORMException
     */
    public function remove(int $id): void
    {
        /** @var object $archiveSeo */
        $archiveSeo = $this->getEntityManager()->getReference(
            $this->getClassName(),
            $id
        );

        $this->getEntityManager()->remove($archiveSeo);
        $this->getEntityManager()->flush();
    }

    /**
     * @param ArchiveSeo $archiveSeo
     * @return ArchiveSeo
     */
    public function save(ArchiveSeo $archiveSeo): ArchiveSeo
    {
        $this->getEntityManager()->persist($archiveSeo);
        $this->getEntityManager()->flush();
        return $archiveSeo;
    }

    public function findById(int $id, string $locale): ?ArchiveSeo
    {
        $archiveSeo = $this->find($id);
        if (!$archiveSeo) {
            return null;
        }

        $archiveSeo->setLocale($locale);

        return $archiveSeo;
    }

}
