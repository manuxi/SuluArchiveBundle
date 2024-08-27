<?php

declare(strict_types=1);

namespace Manuxi\SuluArchiveBundle\Content;

use JMS\Serializer\Annotation as Serializer;
use Manuxi\SuluArchiveBundle\Entity\Archive;
use Sulu\Component\SmartContent\ItemInterface;

#[Serializer\ExclusionPolicy("all")]
class ArchiveDataItem implements ItemInterface
{

    private Archive $entity;

    public function __construct(Archive $entity)
    {
        $this->entity = $entity;
    }

    #[Serializer\VirtualProperty]
    public function getId(): string
    {
        return (string) $this->entity->getId();
    }

    #[Serializer\VirtualProperty]
    public function getTitle(): string
    {
        return (string) $this->entity->getTitle();
    }

    #[Serializer\VirtualProperty]
    public function getImage(): ?string
    {
        return null;
    }

    public function getResource(): Archive
    {
        return $this->entity;
    }
}
