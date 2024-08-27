<?php

declare(strict_types=1);

namespace Manuxi\SuluArchiveBundle\Entity\Interfaces;

use Manuxi\SuluArchiveBundle\Entity\ArchiveExcerpt;
use Symfony\Component\HttpFoundation\Request;

interface ArchiveExcerptModelInterface
{
    public function updateArchiveExcerpt(ArchiveExcerpt $archiveExcerpt, Request $request): ArchiveExcerpt;
}
