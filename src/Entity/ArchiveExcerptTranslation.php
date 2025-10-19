<?php

declare(strict_types=1);

namespace Manuxi\SuluArchiveBundle\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\InverseJoinColumn;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\JoinTable;
use Doctrine\ORM\Mapping\ManyToMany;
use Manuxi\SuluArchiveBundle\Repository\ArchiveExcerptTranslationRepository;
use Manuxi\SuluSharedToolsBundle\Entity\Abstracts\Entity\AbstractExcerptTranslation;
use Manuxi\SuluSharedToolsBundle\Entity\Interfaces\ExcerptTranslationInterface;
use Sulu\Bundle\CategoryBundle\Entity\Category;
use Sulu\Bundle\MediaBundle\Entity\MediaInterface;
use Sulu\Bundle\TagBundle\Tag\TagInterface;

#[ORM\Entity(repositoryClass: ArchiveExcerptTranslationRepository::class)]
#[ORM\Table(name: 'app_archive_excerpt_translation')]
class ArchiveExcerptTranslation extends AbstractExcerptTranslation implements ExcerptTranslationInterface
{
    #[ManyToMany(targetEntity: Category::class)]
    #[JoinTable(name: 'app_archive_excerpt_categories')]
    #[JoinColumn(name: 'excerpt_id', referencedColumnName: 'id')]
    #[InverseJoinColumn(name: 'category_id', referencedColumnName: 'id')]
    protected ?Collection $categories = null;

    #[ManyToMany(targetEntity: TagInterface::class)]
    #[JoinTable(name: 'app_archive_excerpt_tags')]
    #[JoinColumn(name: 'excerpt_id', referencedColumnName: 'id')]
    #[InverseJoinColumn(name: 'tag_id', referencedColumnName: 'id')]
    protected ?Collection $tags = null;

    #[ManyToMany(targetEntity: MediaInterface::class)]
    #[JoinTable(name: 'app_archive_excerpt_icons')]
    #[JoinColumn(name: 'excerpt_id', referencedColumnName: 'id')]
    #[InverseJoinColumn(name: 'icon_id', referencedColumnName: 'id')]
    protected ?Collection $icons = null;

    #[ManyToMany(targetEntity: MediaInterface::class)]
    #[JoinTable(name: 'app_archive_excerpt_images')]
    #[JoinColumn(name: 'excerpt_id', referencedColumnName: 'id')]
    #[InverseJoinColumn(name: 'image_id', referencedColumnName: 'id')]
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
