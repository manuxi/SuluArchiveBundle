<?php

declare(strict_types=1);

namespace Manuxi\SuluArchiveBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinColumn;
use JMS\Serializer\Annotation as Serializer;
use Manuxi\SuluArchiveBundle\Entity\Interfaces\ExcerptInterface;
use Manuxi\SuluArchiveBundle\Entity\Interfaces\ExcerptTranslatableInterface;
use Manuxi\SuluArchiveBundle\Entity\Traits\ExcerptTrait;
use Manuxi\SuluArchiveBundle\Entity\Traits\ExcerptTranslatableTrait;
use Manuxi\SuluArchiveBundle\Repository\ArchiveExcerptRepository;

#[ORM\Entity(repositoryClass: ArchiveExcerptRepository::class)]
#[ORM\Table(name: 'app_archive_excerpt')]
class ArchiveExcerpt implements ExcerptInterface, ExcerptTranslatableInterface
{
    use ExcerptTrait;
    use ExcerptTranslatableTrait;

    #[Serializer\Exclude]
    #[ORM\OneToOne(inversedBy: 'archiveExcerpt', targetEntity: Archive::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(name: 'archive_id', referencedColumnName: "id", nullable: false)]
    private ?Archive $archive = null;

    #[Serializer\Exclude]
    #[ORM\OneToMany(mappedBy: 'archiveExcerpt', targetEntity: ArchiveExcerptTranslation::class, cascade: ['all'], indexBy: 'locale')]
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
     * @return ArchiveExcerptTranslation[]
     */
    public function getTranslations(): array
    {
        return $this->translations->toArray();
    }

    protected function getTranslation(string $locale): ?ArchiveExcerptTranslation
    {
        if (!$this->translations->containsKey($locale)) {
            return null;
        }

        return $this->translations->get($locale);
    }

    protected function createTranslation(string $locale): ArchiveExcerptTranslation
    {
        $translation = new ArchiveExcerptTranslation($this, $locale);
        $this->translations->set($locale, $translation);

        return $translation;
    }

}
