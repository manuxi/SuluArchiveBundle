<?php

declare(strict_types=1);

namespace Manuxi\SuluArchiveBundle\Entity\Interfaces;

use Manuxi\SuluArchiveBundle\Entity\Archive;
use Symfony\Component\HttpFoundation\Request;

interface ArchiveModelInterface
{
    public function getArchive(int $id, Request $request = null): Archive;
    public function deleteArchive(Archive $entity): void;
    public function createArchive(Request $request): Archive;
    public function updateArchive(int $id, Request $request): Archive;
    public function publishArchive(int $id, Request $request): Archive;
    public function unpublishArchive(int $id, Request $request): Archive;

}
