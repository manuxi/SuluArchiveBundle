<?php

declare(strict_types=1);

namespace Manuxi\SuluArchiveBundle\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinTable;
use Manuxi\SuluArchiveBundle\Repository\ArchiveExcerptTranslationRepository;
use Manuxi\SuluSharedToolsBundle\Entity\Abstracts\Entity\AbstractExcerptTranslation;
use Manuxi\SuluSharedToolsBundle\Entity\Interfaces\ExcerptTranslationInterface;

#[ORM\Entity(repositoryClass: ArchiveExcerptTranslationRepository::class)]
#[ORM\Table(name: 'app_archive_excerpt_translation')]
class ArchiveExcerptTranslation extends AbstractExcerptTranslation implements ExcerptTranslationInterface
{
    #[JoinTable(name: 'app_archive_excerpt_categories')]
    protected ?Collection $categories = null;

    #[JoinTable(name: 'app_archive_excerpt_tags')]
    protected ?Collection $tags = null;

    #[JoinTable(name: 'app_archive_excerpt_icons')]
    protected ?Collection $icons = null;

    #[JoinTable(name: 'app_archive_excerpt_images')]
    protected ?Collection $images = null;

    #[ORM\ManyToOne(targetEntity: ArchiveExcerpt::class, inversedBy: 'translations')]
    #[ORM\JoinColumn(nullable: false)]
    private ArchiveExcerpt $archiveExcerpt;

    public function __construct(ArchiveExcerpt $archiveExcerpt, string $locale)
    {
        $this->archiveExcerpt = $archiveExcerpt;
        $this->setLocale($locale);
        $this->initExcerptTranslationTrait();
    }

    public function __clone()
    {
        $this->id = null;
    }

    public function getArchiveExcerpt(): ArchiveExcerpt
    {
        return $this->archiveExcerpt;
    }
}
