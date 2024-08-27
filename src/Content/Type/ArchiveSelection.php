<?php

declare(strict_types=1);

namespace Manuxi\SuluArchiveBundle\Content\Type;

use Manuxi\SuluArchiveBundle\Entity\Archive;
use Doctrine\ORM\EntityManagerInterface;
use Sulu\Component\Content\Compat\PropertyInterface;
use Sulu\Component\Content\SimpleContentType;

class ArchiveSelection extends SimpleContentType
{
    protected EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;

        parent::__construct('archive_selection', []);
    }

    /**
     * @param PropertyInterface $property
     * @return Archive[]
     */
    public function getContentData(PropertyInterface $property): array
    {
        $ids = $property->getValue();

        if (empty($ids)) {
            return [];
        }

        $archive = $this->entityManager->getRepository(Archive::class)->findBy(['id' => $ids]);

        $idPositions = \array_flip($ids);
        \usort($archive, static function (Archive $a, Archive $b) use ($idPositions) {
            return $idPositions[$a->getId()] - $idPositions[$b->getId()];
        });

        return $archive;
    }

    public function getViewData(PropertyInterface $property): array
    {
        return [
            'ids' => $property->getValue(),
        ];
    }
}
