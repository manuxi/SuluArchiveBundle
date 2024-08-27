<?php

namespace Manuxi\SuluArchiveBundle\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Sulu\Bundle\MediaBundle\Entity\MediaInterface;

trait DocumentTrait
{

    #[ORM\ManyToOne(targetEntity: MediaInterface::class)]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    #[Serializer\Exclude]
    private ?MediaInterface $document = null;

    public function getDocument(): ?MediaInterface
    {
        return $this->document;
    }

    #[Serializer\VirtualProperty]
    #[Serializer\SerializedName("document")]
    public function getDocumentData(): ?array
    {
        if ($document = $this->getDocument()) {
            return [
                'id' => $document->getId(),
            ];
        }

        return null;

    }

    public function setDocument(?MediaInterface $document): self
    {
        $this->document = $document;
        return $this;
    }
}
