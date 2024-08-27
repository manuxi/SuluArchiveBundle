<?php

declare(strict_types=1);

namespace Manuxi\SuluArchiveBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Manuxi\SuluArchiveBundle\Entity\Interfaces\ExcerptTranslationInterface;
use Manuxi\SuluArchiveBundle\Entity\Traits\ExcerptTranslationTrait;
use Manuxi\SuluArchiveBundle\Repository\ArchiveExcerptTranslationRepository;

#[ORM\Entity(repositoryClass: ArchiveExcerptTranslationRepository::class)]
#[ORM\Table(name: 'app_archive_excerpt_translation')]
class ArchiveExcerptTranslation implements ExcerptTranslationInterface
{
    use ExcerptTranslationTrait;

    #[ORM\ManyToOne(targetEntity: ArchiveExcerpt::class, inversedBy: 'translations')]
    #[ORM\JoinColumn(nullable: false)]
    private ArchiveExcerpt $archiveExcerpt;

    public function __construct(ArchiveExcerpt $archiveExcerpt, string $locale)
    {
        $this->archiveExcerpt = $archiveExcerpt;
        $this->setLocale($locale);
        $this->initExcerptTranslationTrait();
    }

    public function __clone(){
        $this->id = null;
    }

    public function getArchiveExcerpt(): ArchiveExcerpt
    {
        return $this->archiveExcerpt;
    }
}
