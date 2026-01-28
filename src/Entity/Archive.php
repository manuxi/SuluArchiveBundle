<?php

declare(strict_types=1);

namespace Manuxi\SuluArchiveBundle\Entity;

use Sulu\Content\Domain\Model\ContentRichEntityInterface;
use Sulu\Content\Domain\Model\ContentRichEntityTrait;
use Sulu\Content\Domain\Model\DimensionContentInterface;
use Symfony\Component\Uid\Uuid;

/**
 * @implements ContentRichEntityInterface<ArchiveDimensionContent>
 */
class Archive implements ContentRichEntityInterface
{
    /**
     * @phpstan-use ContentRichEntityTrait<ArchiveDimensionContent>
     */
    use ContentRichEntityTrait;

    public const RESOURCE_KEY = 'archives';
    public const FORM_KEY = 'archive_detailed';
    public const LIST_KEY = 'archives';
    public const LIST_KEY_PUBLISHED = 'archives_published';
    public const SECURITY_CONTEXT = 'sulu.archives.archives';
    public const TEMPLATE_TYPE = 'archive';

    protected string $uuid;

    public function __construct(?string $uuid = null)
    {
        $this->uuid = $uuid ?: Uuid::v7()->toRfc4122();
        $this->initializeDimensionContents();
    }

    public function getId(): string
    {
        return $this->uuid;
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function createDimensionContent(): DimensionContentInterface
    {
        return new ArchiveDimensionContent($this);
    }
}
