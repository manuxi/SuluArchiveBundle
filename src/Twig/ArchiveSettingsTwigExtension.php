<?php

namespace Manuxi\SuluArchiveBundle\Twig;

use Doctrine\ORM\EntityManagerInterface;

use Manuxi\SuluArchiveBundle\Entity\ArchiveSettings;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ArchiveSettingsTwigExtension extends AbstractExtension
{
    private EntityManagerInterface $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager
    ) {
        $this->entityManager = $entityManager;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('load_archive_settings', [$this, 'loadArchiveSettings']),
        ];
    }

    public function loadArchiveSettings(): ArchiveSettings
    {
        $archiveSettings = $this->entityManager->getRepository(ArchiveSettings::class)->findOneBy([]) ?? null;

        return $archiveSettings ?: new ArchiveSettings();
    }
}