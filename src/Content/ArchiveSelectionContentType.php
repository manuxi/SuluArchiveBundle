<?php

declare(strict_types=1);

namespace Manuxi\SuluArchiveBundle\Content;

use Manuxi\SuluArchiveBundle\Entity\Archive;
use Manuxi\SuluArchiveBundle\Repository\ArchiveRepository;
use Sulu\Component\Content\Compat\PropertyInterface;
use Sulu\Component\Content\SimpleContentType;

class ArchiveSelectionContentType extends SimpleContentType
{
    private ArchiveRepository $archiveRepository;

    public function __construct(ArchiveRepository $archiveRepository)
    {
        parent::__construct('archive_selection');

        $this->archiveRepository = $archiveRepository;
    }

    /**
     * @param PropertyInterface $property
     * @return Archive[]
     */
    public function getContentData(PropertyInterface $property): array
    {
        $ids = $property->getValue();
        $locale = $property->getStructure()->getLanguageCode();

        $archivelist = [];
        foreach ($ids ?: [] as $id) {
            $archive = $this->archiveRepository->findById((int) $id, $locale);
            if ($archive && $archive->isPublished()) {
                $archivelist[] = $archive;
            }
        }
        return $archivelist;
    }

    /**
     * @param PropertyInterface $property
     * @return mixed[]
     */
    public function getViewData(PropertyInterface $property): array
    {
        return $property->getValue();
    }
}
