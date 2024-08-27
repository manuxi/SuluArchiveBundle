<?php

declare(strict_types=1);

namespace Manuxi\SuluArchiveBundle\Twig;

use Manuxi\SuluArchiveBundle\Entity\Archive;
use Manuxi\SuluArchiveBundle\Repository\ArchiveRepository;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ArchiveTwigExtension extends AbstractExtension
{
    private ArchiveRepository $archiveRepository;

    public function __construct(ArchiveRepository $archiveRepository)
    {
        $this->archiveRepository = $archiveRepository;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('sulu_resolve_archive', [$this, 'resolveArchive']),
            new TwigFunction('sulu_get_archive', [$this, 'getArchive'])
        ];
    }

    public function resolveArchive(int $id, string $locale = 'en'): ?Archive
    {
        $archive = $this->archiveRepository->findById($id, $locale);

        return $archive ?? null;
    }

    public function getArchive(int $limit = 8, $locale = 'en'): array
    {
        return $this->archiveRepository->findByFilters([], 0, $limit, $limit, $locale);
    }
}