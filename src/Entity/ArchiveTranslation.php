<?php

declare(strict_types=1);

namespace Manuxi\SuluArchiveBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Manuxi\SuluArchiveBundle\Entity\Traits\LinkTrait;
use Manuxi\SuluArchiveBundle\Entity\Interfaces\AuditableInterface;
use Manuxi\SuluArchiveBundle\Entity\Traits\AuditableTrait;
use Manuxi\SuluArchiveBundle\Entity\Traits\ImageTrait;
use Manuxi\SuluArchiveBundle\Entity\Traits\DocumentTrait;
use Manuxi\SuluArchiveBundle\Entity\Traits\PublishedTrait;
use Manuxi\SuluArchiveBundle\Entity\Traits\RoutePathTrait;
use Manuxi\SuluArchiveBundle\Entity\Traits\ShowAuthorTrait;
use Manuxi\SuluArchiveBundle\Entity\Traits\ShowDateTrait;
use Manuxi\SuluArchiveBundle\Repository\ArchiveTranslationRepository;

#[ORM\Entity(repositoryClass: ArchiveTranslationRepository::class)]
#[ORM\Table(name: 'app_archive_translation')]
class ArchiveTranslation implements AuditableInterface
{
    use AuditableTrait;
    use PublishedTrait;
    use RoutePathTrait;
    use LinkTrait;
    use ShowAuthorTrait;
    use ShowDateTrait;
    use DocumentTrait;
    use ImageTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Archive::class, inversedBy: 'translations')]
    #[ORM\JoinColumn(nullable: false)]
    private Archive $archive;

    #[ORM\Column(type: Types::STRING, length: 5)]
    private string $locale;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $title = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $subtitle = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $summary = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $text = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $footer = null;

    public function __construct(Archive $archive, string $locale)
    {
        $this->archive  = $archive;
        $this->locale = $locale;
    }

    public function __clone(){
        $this->id = null;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getArchive(): Archive
    {
        return $this->archive;
    }

    public function setArchive(Archive $archive): self
    {
        $this->archive = $archive;
        return $this;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function setLocale(string $locale): self
    {
        $this->locale = $locale;
        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function getSubtitle(): ?string
    {
        return $this->subtitle;
    }

    public function setSubtitle(?string $subtitle): self
    {
        $this->subtitle = $subtitle;
        return $this;
    }

    public function getSummary(): ?string
    {
        return $this->summary;
    }

    public function setSummary(?string $summary): self
    {
        $this->summary = $summary;
        return $this;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(?string $text): self
    {
        $this->text = $text;
        return $this;
    }

    public function getFooter(): ?string
    {
        return $this->footer;
    }

    public function setFooter(?string $footer): self
    {
        $this->footer = $footer;
        return $this;
    }


}
