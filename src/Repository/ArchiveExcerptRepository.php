<?php

declare(strict_types=1);

namespace Manuxi\SuluArchiveBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use Manuxi\SuluArchiveBundle\Entity\ArchiveExcerpt;

/**
 * @method ArchiveExcerpt|null find($id, $lockMode = null, $lockVersion = null)
 * @method ArchiveExcerpt|null findOneBy(array $criteria, array $orderBy = null)
 * @method ArchiveExcerpt[]    findAll()
 * @method ArchiveExcerpt[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @extends ServiceEntityRepository<Archive>
 */
class ArchiveExcerptRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ArchiveExcerpt::class);
    }

    public function create(string $locale): ArchiveExcerpt
    {
        $archiveExcerpt = new ArchiveExcerpt();
        $archiveExcerpt->setLocale($locale);

        return $archiveExcerpt;
    }

    /**
     * @param int $id
     * @throws \Doctrine\ORM\Exception\ORMException
     */
    public function remove(int $id): void
    {
        /** @var object $archiveExcerpt */
        $archiveExcerpt = $this->getEntityManager()->getReference(
            $this->getClassName(),
            $id
        );

        $this->getEntityManager()->remove($archiveExcerpt);
        $this->getEntityManager()->flush();
    }

    /**
     * @param ArchiveExcerpt $archiveExcerpt
     * @return ArchiveExcerpt
     */
    public function save(ArchiveExcerpt $archiveExcerpt): ArchiveExcerpt
    {
        $this->getEntityManager()->persist($archiveExcerpt);
        $this->getEntityManager()->flush();
        return $archiveExcerpt;
    }

    public function findById(int $id, string $locale): ?ArchiveExcerpt
    {
        $archiveExcerpt = $this->find($id);
        if (!$archiveExcerpt) {
            return null;
        }

        $archiveExcerpt->setLocale($locale);

        return $archiveExcerpt;
    }

}
