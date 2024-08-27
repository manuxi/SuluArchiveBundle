<?php

declare(strict_types=1);

namespace Manuxi\SuluArchiveBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinColumn;
use JMS\Serializer\Annotation as Serializer;
use Manuxi\SuluArchiveBundle\Entity\Interfaces\SeoInterface;
use Manuxi\SuluArchiveBundle\Entity\Interfaces\SeoTranslatableInterface;
use Manuxi\SuluArchiveBundle\Entity\Traits\SeoTrait;
use Manuxi\SuluArchiveBundle\Entity\Traits\SeoTranslatableTrait;
use Manuxi\SuluArchiveBundle\Repository\ArchiveSeoRepository;

#[ORM\Entity(repositoryClass: ArchiveSeoRepository::class)]
#[ORM\Table(name: 'app_archive_seo')]
class ArchiveSeo implements SeoInterface, SeoTranslatableInterface
{
    use SeoTrait;
    use SeoTranslatableTrait;

    #[Serializer\Exclude]
    #[ORM\OneToOne(inversedBy: 'archiveSeo', targetEntity: Archive::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(name: 'archive_id', referencedColumnName: "id", nullable: false)]
    private ?Archive $archive = null;

    #[Serializer\Exclude]
    #[ORM\OneToMany(mappedBy: 'archiveSeo', targetEntity: ArchiveSeoTranslation::class, cascade: ['all'], indexBy: 'locale')]
    private Collection $translations;

    public function __construct()
    {
        $this->translations = new ArrayCollection();
    }

    public function __clone(){
        $this->id = null;
    }

    public function getArchive(): ?Archive
    {
        return $this->archive;
    }

    public function setArchive(Archive $archive): self
    {
        $this->archive = $archive;
        return $this;
    }

    /**
     * @return ArchiveSeoTranslation[]
     */
    public function getTranslations(): array
    {
        return $this->translations->toArray();
    }

    protected function getTranslation(string $locale): ?ArchiveSeoTranslation
    {
        if (!$this->translations->containsKey($locale)) {
            return null;
        }

        return $this->translations->get($locale);
    }

    protected function createTranslation(string $locale): ArchiveSeoTranslation
    {
        $translation = new ArchiveSeoTranslation($this, $locale);
        $this->translations->set($locale, $translation);

        return $translation;
    }
}
