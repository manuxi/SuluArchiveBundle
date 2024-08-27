<?php

declare(strict_types=1);

namespace Manuxi\SuluArchiveBundle\Entity\Traits;

use JMS\Serializer\Annotation as Serializer;
use Sulu\Bundle\MediaBundle\Entity\MediaInterface;

trait DocumentTranslatableTrait
{
    abstract public function getLocale();
    abstract protected function getTranslation(string $locale);

    #[Serializer\Exclude]
    public function getDocument(): ?MediaInterface
    {
        $translation = $this->getTranslation($this->getLocale());
        if (!$translation) {
            return null;
        }

        return $translation->getDocument();
    }

    #[Serializer\VirtualProperty]
    #[Serializer\SerializedName("document")]
    public function getDocumentData(): ?array
    {
        $translation = $this->getTranslation($this->getLocale());
        if (!$translation) {
            return null;
        }

        return $translation->getDocumentData();
    }

    public function setDocument(?MediaInterface $document): self
    {
        $translation = $this->getTranslation($this->getLocale());
        if (!$translation) {
            $translation = $this->createTranslation($this->getLocale());
        }

        $translation->setDocument($document);
        return $this;
    }
}
