<?php

declare(strict_types=1);

namespace Manuxi\SuluArchiveBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Manuxi\SuluArchiveBundle\Entity\Traits\LinkTranslatableTrait;
use Manuxi\SuluArchiveBundle\Entity\Interfaces\AuditableTranslatableInterface;
use Manuxi\SuluArchiveBundle\Entity\Traits\AuditableTranslatableTrait;
use Manuxi\SuluArchiveBundle\Entity\Traits\ImageTranslatableTrait;
use Manuxi\SuluArchiveBundle\Entity\Traits\DocumentTranslatableTrait;
use Manuxi\SuluArchiveBundle\Entity\Traits\PublishedTranslatableTrait;
use Manuxi\SuluArchiveBundle\Entity\Traits\RoutePathTranslatableTrait;
use Manuxi\SuluArchiveBundle\Entity\Traits\ShowAuthorTranslatableTrait;
use Manuxi\SuluArchiveBundle\Entity\Traits\ShowDateTranslatableTrait;
use Manuxi\SuluArchiveBundle\Entity\Traits\TypeTrait;
use Manuxi\SuluArchiveBundle\Repository\ArchiveRepository;

#[ORM\Entity(repositoryClass: ArchiveRepository::class)]
#[ORM\Table(name: 'app_archive')]
class Archive implements AuditableTranslatableInterface
{
    public const RESOURCE_KEY = 'archive';
    public const FORM_KEY = 'archive_details';
    public const LIST_KEY = 'archive';
    public const SECURITY_CONTEXT = 'sulu.archive.archive';

    use AuditableTranslatableTrait;
    use PublishedTranslatableTrait;
    use RoutePathTranslatableTrait;
    use ShowAuthorTranslatableTrait;
    use ShowDateTranslatableTrait;
    use DocumentTranslatableTrait;
    use LinkTranslatableTrait;
    use ImageTranslatableTrait;
    use TypeTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[Serializer\Exclude]
    #[ORM\OneToOne(mappedBy: 'archive', targetEntity: ArchiveSeo::class, cascade: ['persist', 'remove'])]
    private ?ArchiveSeo $archiveSeo = null;

    #[Serializer\Exclude]
    #[ORM\OneToOne(mappedBy: 'archive', targetEntity: ArchiveExcerpt::class, cascade: ['persist', 'remove'])]
    private ?ArchiveExcerpt $archiveExcerpt = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $images = null;

    #[Serializer\Exclude]
    #[ORM\OneToMany(mappedBy: 'archive', targetEntity: ArchiveTranslation::class, cascade: ['all'], fetch: 'EXTRA_LAZY', indexBy: 'locale')]
    private Collection $translations;

    private string $locale = 'de';

    private array $ext = [];

    public function __construct()
    {
        $this->translations = new ArrayCollection();
        $this->initExt();
    }

    public function __clone(){
        $this->id = null;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    #[Serializer\VirtualProperty(name: "title")]
    public function getTitle(): ?string
    {
        $translation = $this->getTranslation($this->locale);
        if (!$translation) {
            return null;
        }

        return $translation->getTitle();
    }

    public function setTitle(string $title): self
    {
        $translation = $this->getTranslation($this->locale);
        if (!$translation) {
            $translation = $this->createTranslation($this->locale);
        }

        $translation->setTitle($title);
        return $this;
    }

    #[Serializer\VirtualProperty(name: "subtitle")]
    public function getSubtitle(): ?string
    {
        $translation = $this->getTranslation($this->locale);
        if (!$translation) {
            return null;
        }

        return $translation->getSubtitle();
    }

    public function setSubtitle(?string $subtitle): self
    {
        $translation = $this->getTranslation($this->locale);
        if (!$translation) {
            $translation = $this->createTranslation($this->locale);
        }

        $translation->setSubtitle($subtitle);
        return $this;
    }

    #[Serializer\VirtualProperty(name: "summary")]
    public function getSummary(): ?string
    {
        $translation = $this->getTranslation($this->locale);
        if (!$translation) {
            return null;
        }

        return $translation->getSummary();
    }

    public function setSummary(?string $summary): self
    {
        $translation = $this->getTranslation($this->locale);
        if (!$translation) {
            $translation = $this->createTranslation($this->locale);
        }

        $translation->setSummary($summary);
        return $this;
    }

    #[Serializer\VirtualProperty(name: "text")]
    public function getText(): ?string
    {
        $translation = $this->getTranslation($this->locale);
        if (!$translation) {
            return null;
        }

        return $translation->getText();
    }

    public function setText(string $text): self
    {
        $translation = $this->getTranslation($this->locale);
        if (!$translation) {
            $translation = $this->createTranslation($this->locale);
        }

        $translation->setText($text);
        return $this;
    }

    #[Serializer\VirtualProperty(name: "footer")]
    public function getFooter(): ?string
    {
        $translation = $this->getTranslation($this->locale);
        if (!$translation) {
            return null;
        }

        return $translation->getFooter();
    }

    public function setFooter(?string $footer): self
    {
        $translation = $this->getTranslation($this->locale);
        if (!$translation) {
            $translation = $this->createTranslation($this->locale);
        }

        $translation->setFooter($footer);
        return $this;
    }

    public function getArchiveSeo(): ArchiveSeo
    {
        if (!$this->archiveSeo instanceof ArchiveSeo) {
            $this->archiveSeo = new ArchiveSeo();
            $this->archiveSeo->setArchive($this);
        }

        return $this->archiveSeo;
    }

    public function setArchiveSeo(?ArchiveSeo $archiveSeo): self
    {
        $this->archiveSeo = $archiveSeo;
        return $this;
    }

    public function getArchiveExcerpt(): ArchiveExcerpt
    {
        if (!$this->archiveExcerpt instanceof ArchiveExcerpt) {
            $this->archiveExcerpt = new ArchiveExcerpt();
            $this->archiveExcerpt->setArchive($this);
        }

        return $this->archiveExcerpt;
    }

    public function setArchiveExcerpt(?ArchiveExcerpt $archiveExcerpt): self
    {
        $this->archiveExcerpt = $archiveExcerpt;
        return $this;
    }

    #[Serializer\VirtualProperty(name: "ext")]
    public function getExt(): array
    {
        return $this->ext;
    }

    public function setExt(array $ext): self
    {
        $this->ext = $ext;
        return $this;
    }

    public function addExt(string $key, $value): self
    {
        $this->ext[$key] = $value;
        return $this;
    }

    public function hasExt(string $key): bool
    {
        return \array_key_exists($key, $this->ext);
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function setLocale(string $locale): self
    {
        $this->locale = $locale;
        $this->propagateLocale($locale);
        return $this;
    }

    /**
     * @return ArchiveTranslation[]
     */
    public function getTranslations(): array
    {
        return $this->translations->toArray();
    }

    public function setTranslation(ArchiveTranslation $translation, string $locale): self
    {
        $this->translations->set($locale, $translation);
        return $this;
    }

    protected function getTranslation(string $locale): ?ArchiveTranslation
    {
        if (!$this->translations->containsKey($locale)) {
            return null;
        }

        return $this->translations->get($locale);
    }

    protected function createTranslation(string $locale): ArchiveTranslation
    {
        $translation = new ArchiveTranslation($this, $locale);
        $this->translations->set($locale, $translation);
        return $translation;
    }

    private function propagateLocale(string $locale): self
    {
        $archiveSeo = $this->getArchiveSeo();
        $archiveSeo->setLocale($locale);
        $archiveExcerpt = $this->getArchiveExcerpt();
        $archiveExcerpt->setLocale($locale);
        $this->initExt();
        return $this;
    }

    private function initExt(): self
    {
        if (!$this->hasExt('seo')) {
            $this->addExt('seo', $this->getArchiveSeo());
        }
        if (!$this->hasExt('excerpt')) {
            $this->addExt('excerpt', $this->getArchiveExcerpt());
        }

        return $this;
    }

    #[Serializer\VirtualProperty(name: "availableLocales")]
    public function getAvailableLocales(): array
    {
        return \array_values($this->translations->getKeys());
    }

    public function copy(Archive $copy): Archive
    {

        $copy->setType($this->getType());

        if ($currentTranslation = $this->getTranslation($this->getLocale())) {
            $newTranslation = clone $currentTranslation;
            $copy->setTranslation($newTranslation);

            //copy ext also...
            foreach($this->ext as $key => $translatable) {
                $copy->addExt($key, clone $translatable);
            }
        }
        return $copy;

    }

    public function copyToLocale(string $locale): self
    {
        if ($currentTranslation = $this->getTranslation($this->getLocale())) {
           $newTranslation = clone $currentTranslation;
           $newTranslation->setLocale($locale);
           $this->translations->set($locale, $newTranslation);

           //copy ext also...
           foreach($this->ext as $translatable) {
               $translatable->copyToLocale($locale);
           }

           $this->setLocale($locale);
        }
        return $this;
    }

    public function getImages(): array
    {
        return $this->images ?? [];
    }

    public function setImages(?array $images): self
    {
        $this->images = $images;
        return $this;
    }

}
