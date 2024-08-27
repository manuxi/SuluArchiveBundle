<?php

declare(strict_types=1);

namespace Manuxi\SuluArchiveBundle\Content\Type;

use Manuxi\SuluArchiveBundle\Entity\Archive;
use Doctrine\ORM\EntityManagerInterface;
use Sulu\Component\Content\Compat\PropertyInterface;
use Sulu\Component\Content\SimpleContentType;

class SingleArchiveSelection extends SimpleContentType
{
    protected EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;

        parent::__construct('single_archive_selection');
    }

    public function getContentData(PropertyInterface $property): ?Archive
    {
        $id = $property->getValue();

        if (empty($id)) {
            return null;
        }

        return $this->entityManager->getRepository(Archive::class)->find($id);
    }

    public function getViewData(PropertyInterface $property): array
    {
        return [
            'id' => $property->getValue(),
        ];
    }
}
