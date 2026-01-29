<?php

declare(strict_types=1);

namespace Manuxi\SuluArchiveBundle\Entity;

use JMS\Serializer\Annotation as Serializer;
use Sulu\Bundle\ContactBundle\Entity\ContactInterface;
use Sulu\Bundle\MediaBundle\Entity\MediaInterface;
use Sulu\Content\Domain\Model\AuditableInterface;
use Sulu\Content\Domain\Model\AuditableTrait;
use Sulu\Content\Domain\Model\AuthorInterface;
use Sulu\Content\Domain\Model\AuthorTrait;
use Sulu\Content\Domain\Model\ContentRichEntityInterface;
use Sulu\Content\Domain\Model\DimensionContentInterface;
use Sulu\Content\Domain\Model\DimensionContentTrait;
use Sulu\Content\Domain\Model\ExcerptInterface;
use Sulu\Content\Domain\Model\ExcerptTrait;
use Sulu\Content\Domain\Model\LinkInterface;
use Sulu\Content\Domain\Model\LinkTrait;
use Sulu\Content\Domain\Model\RoutableInterface;
use Sulu\Content\Domain\Model\RoutableTrait;
use Sulu\Content\Domain\Model\SeoInterface;
use Sulu\Content\Domain\Model\SeoTrait;
use Sulu\Content\Domain\Model\ShadowInterface;
use Sulu\Content\Domain\Model\ShadowTrait;
use Sulu\Content\Domain\Model\TaxonomyInterface;
use Sulu\Content\Domain\Model\TaxonomyTrait;
use Sulu\Content\Domain\Model\TemplateInterface;
use Sulu\Content\Domain\Model\TemplateTrait;
use Sulu\Content\Domain\Model\WebspaceInterface;
use Sulu\Content\Domain\Model\WebspaceTrait;
use Sulu\Content\Domain\Model\WorkflowInterface;
use Sulu\Content\Domain\Model\WorkflowTrait;
use Symfony\Component\Serializer\Attribute\Ignore;

/**
 * @implements DimensionContentInterface<Archive>
 */
class ArchiveDimensionContent implements DimensionContentInterface, ExcerptInterface, TaxonomyInterface, SeoInterface, TemplateInterface, RoutableInterface, WorkflowInterface, AuthorInterface, WebspaceInterface, ShadowInterface, AuditableInterface, LinkInterface
{
    use AuthorTrait;
    use DimensionContentTrait;
    use ExcerptTrait;
    use TaxonomyTrait;
    use RoutableTrait;
    use SeoTrait;
    use ShadowTrait;
    use TemplateTrait {
        TemplateTrait::setTemplateData as parentSetTemplateData;
    }
    use WebspaceTrait;
    use WorkflowTrait;
    use AuditableTrait;
    use LinkTrait;

    protected ?int $id = null;

    #[Ignore]
    protected Archive $archive;

    protected ?string $type = 'default';
    protected ?string $link = null;
    protected ?string $title = null;
    protected ?string $subtitle = null;
    protected ?string $summary = null;
    protected ?string $text = null;
    protected ?string $footer = null;
    protected ?MediaInterface $image = null;
    protected ?array $images = null;
    protected ?MediaInterface $document = null;
    protected ?bool $showAuthor = false;
    protected ?bool $showDate = false;

    public function __construct(Archive $archive)
    {
        $this->archive = $archive;
        $this->created = new \DateTimeImmutable();
        $this->changed = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getResource(): ContentRichEntityInterface
    {
        return $this->archive;
    }

    public function getArchive(): Archive
    {
        return $this->archive;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;

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

    #[Serializer\Groups(['default', 'admin', 'fullArchive', 'partialArchive'])]
    public function getImage(): ?MediaInterface
    {
        return $this->image;
    }

    public function setImage(?MediaInterface $image): self
    {
        $this->image = $image;

        return $this;
    }

    #[Serializer\Groups(['default', 'admin', 'fullArchive', 'partialArchive'])]
    public function getImages(): ?array
    {
        return $this->images ?? [];
    }

    public function setImages(?array $images): self
    {
        $this->images = $images;

        return $this;
    }

    #[Serializer\Groups(['default', 'admin', 'fullArchive', 'partialArchive'])]
    public function getDocument(): ?MediaInterface
    {
        return $this->document;
    }

    public function setDocument(?MediaInterface $document): self
    {
        $this->document = $document;

        return $this;
    }

    public function getShowAuthor(): ?bool
    {
        return $this->showAuthor;
    }

    public function setShowAuthor(?bool $showAuthor): self
    {
        $this->showAuthor = $showAuthor;

        return $this;
    }

    public function getShowDate(): ?bool
    {
        return $this->showDate;
    }

    public function setShowDate(?bool $showDate): self
    {
        $this->showDate = $showDate;

        return $this;
    }

    #[Serializer\Groups(['default', 'admin', 'fullArchive', 'partialArchive'])]
    public function getLink(): ?string
    {
        return $this->link;
    }

    public function setLink(?string $link): self
    {
        $this->link = $link;

        return $this;
    }

    public function copyAttributesFrom(DimensionContentInterface $dimensionContent): void
    {
        if (!$dimensionContent instanceof self) {
            return;
        }

        $this->type = $dimensionContent->type;
        $this->title = $dimensionContent->title;
        $this->subtitle = $dimensionContent->subtitle;
        $this->summary = $dimensionContent->summary;
        $this->text = $dimensionContent->text;
        $this->footer = $dimensionContent->footer;
        $this->image = $dimensionContent->image;
        $this->images = $dimensionContent->images;
        $this->document = $dimensionContent->document;
        $this->showAuthor = $dimensionContent->showAuthor;
        $this->showDate = $dimensionContent->showDate;
        $this->author = $dimensionContent->author;
        $this->authored = $dimensionContent->authored;
        $this->workflowPlace = $dimensionContent->workflowPlace;
        $this->workflowPublished = $dimensionContent->workflowPublished;
    }

    public function setTemplateData(array $templateData): void
    {
        if (\array_key_exists('type', $templateData)) {
            $this->type = \is_string($templateData['type']) ? $templateData['type'] : null;
        }

        if (\array_key_exists('title', $templateData)) {
            $this->title = \is_string($templateData['title']) ? $templateData['title'] : null;
        }

        if (\array_key_exists('subtitle', $templateData)) {
            $this->subtitle = \is_string($templateData['subtitle']) ? $templateData['subtitle'] : null;
        }

        if (\array_key_exists('summary', $templateData)) {
            $this->summary = \is_string($templateData['summary']) ? $templateData['summary'] : null;
        }

        if (\array_key_exists('text', $templateData)) {
            $this->text = \is_string($templateData['text']) ? $templateData['text'] : null;
        }

        if (\array_key_exists('footer', $templateData)) {
            $this->footer = \is_string($templateData['footer']) ? $templateData['footer'] : null;
        }

        if (\array_key_exists('images', $templateData)) {
            $this->images = \is_array($templateData['images']) ? $templateData['images'] : null;
        }

        if (\array_key_exists('showAuthor', $templateData)) {
            $this->showAuthor = \filter_var(
                $templateData['showAuthor'],
                FILTER_VALIDATE_BOOLEAN,
                FILTER_NULL_ON_FAILURE
            );
        }

        if (\array_key_exists('showDate', $templateData)) {
            $this->showDate = \filter_var(
                $templateData['showDate'],
                FILTER_VALIDATE_BOOLEAN,
                FILTER_NULL_ON_FAILURE
            );
        }

        if (\array_key_exists('image', $templateData)) {
            $this->image = $templateData['image'] instanceof MediaInterface ? $templateData['image'] : null;
        }

        if (\array_key_exists('document', $templateData)) {
            $this->document = $templateData['document'] instanceof MediaInterface ? $templateData['document'] : null;
        }

        if (\array_key_exists('authored', $templateData)) {
            if ($templateData['authored'] instanceof \DateTimeImmutable) {
                $this->authored = $templateData['authored'];
            } elseif (\is_string($templateData['authored']) && !empty($templateData['authored'])) {
                try {
                    $this->authored = new \DateTimeImmutable($templateData['authored']);
                } catch (\Exception $e) {
                    $this->authored = null;
                }
            } else {
                $this->authored = null;
            }
        }

        if (\array_key_exists('author', $templateData)) {
            $this->author = $templateData['author'] instanceof ContactInterface ? $templateData['author'] : null;
        }

        $this->parentSetTemplateData($templateData);
    }

    public static function getTemplateType(): string
    {
        return Archive::TEMPLATE_TYPE;
    }

    public static function getResourceKey(): string
    {
        return Archive::RESOURCE_KEY;
    }
}
