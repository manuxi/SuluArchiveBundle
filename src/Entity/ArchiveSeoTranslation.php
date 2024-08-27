<?php

declare(strict_types=1);

namespace Manuxi\SuluArchiveBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Manuxi\SuluArchiveBundle\Entity\Interfaces\SeoTranslationInterface;
use Manuxi\SuluArchiveBundle\Entity\Traits\SeoTranslationTrait;
use Manuxi\SuluArchiveBundle\Repository\ArchiveSeoTranslationRepository;

#[ORM\Entity(repositoryClass: ArchiveSeoTranslationRepository::class)]
#[ORM\Table(name: 'app_archive_seo_translation')]
class ArchiveSeoTranslation implements SeoTranslationInterface
{
    use SeoTranslationTrait;

    #[ORM\ManyToOne(targetEntity: ArchiveSeo::class, inversedBy: 'translations')]
    #[ORM\JoinColumn(nullable: false)]
    private ArchiveSeo $archiveSeo;

    public function __construct(ArchiveSeo $archiveSeo, string $locale)
    {
        $this->archiveSeo = $archiveSeo;
        $this->setLocale($locale);
    }

    public function __clone(){
        $this->id = null;
    }

    public function getArchiveSeo(): ArchiveSeo
    {
        return $this->archiveSeo;
    }

}
