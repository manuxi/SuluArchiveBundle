<?php

declare(strict_types=1);

namespace Manuxi\SuluArchiveBundle\Application\Mapper;

use Manuxi\SuluArchiveBundle\Entity\Archive;

interface ArchiveMapperInterface
{
    public function mapArchiveData(Archive $archive, array $data): void;
}
