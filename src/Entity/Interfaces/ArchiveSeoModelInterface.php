<?php

declare(strict_types=1);

namespace Manuxi\SuluArchiveBundle\Entity\Interfaces;

use Manuxi\SuluArchiveBundle\Entity\ArchiveSeo;
use Symfony\Component\HttpFoundation\Request;

interface ArchiveSeoModelInterface
{
    public function updateArchiveSeo(ArchiveSeo $archiveSeo, Request $request): ArchiveSeo;
}
