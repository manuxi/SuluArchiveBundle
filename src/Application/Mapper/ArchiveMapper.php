<?php

declare(strict_types=1);

namespace Manuxi\SuluArchiveBundle\Application\Mapper;

use Manuxi\SuluArchiveBundle\Entity\Archive;
use Sulu\Content\Application\ContentManager\ContentManagerInterface;
use Sulu\Content\Domain\Model\DimensionContentInterface;

class ArchiveMapper implements ArchiveMapperInterface
{
    public function __construct(
        private readonly ContentManagerInterface $contentManager,
    ) {
    }

    public function mapArchiveData(Archive $archive, array $data): void
    {
        $locale = $data['locale'] ?? null;
        if (!$locale) {
            return;
        }

        $dimensionAttributes = [
            'locale' => $locale,
            'stage' => DimensionContentInterface::STAGE_DRAFT,
        ];

        $this->contentManager->persist($archive, $data, $dimensionAttributes);
    }
}
