<?php

declare(strict_types=1);

namespace Manuxi\SuluArchiveBundle\Content\DataMapper;

use Doctrine\ORM\EntityManagerInterface;
use Manuxi\SuluArchiveBundle\Entity\ArchiveDimensionContent;
use Sulu\Bundle\ContactBundle\Entity\ContactInterface;
use Sulu\Bundle\MediaBundle\Entity\Media;
use Sulu\Content\Application\ContentDataMapper\DataMapper\DataMapperInterface;
use Sulu\Content\Domain\Model\DimensionContentInterface;

/**
 * Maps unlocalized Archive data (type, image, document, flags) to dimension content.
 * These fields are shared across all locales.
 */
class ArchiveUnlocalizedDataMapper implements DataMapperInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function map(
        DimensionContentInterface $unlocalizedDimensionContent,
        DimensionContentInterface $localizedDimensionContent,
        array $data,
    ): void {
        if (!$localizedDimensionContent instanceof ArchiveDimensionContent) {
            return;
        }

        if (!$unlocalizedDimensionContent instanceof ArchiveDimensionContent) {
            return;
        }

        $this->mapType($unlocalizedDimensionContent, $localizedDimensionContent, $data);
        $this->mapImage($unlocalizedDimensionContent, $localizedDimensionContent, $data);
        $this->mapDocument($unlocalizedDimensionContent, $localizedDimensionContent, $data);
        $this->mapAuthor($localizedDimensionContent, $data);
        $this->mapShowFlags($unlocalizedDimensionContent, $localizedDimensionContent, $data);
    }

    private function mapType(
        ArchiveDimensionContent $unlocalizedContent,
        ArchiveDimensionContent $localizedContent,
        array $data
    ): void {
        if (!\array_key_exists('type', $data)) {
            return;
        }

        $type = $data['type'];
        $unlocalizedContent->setType($type);
        $localizedContent->setType($type);
    }

    private function mapImage(
        ArchiveDimensionContent $unlocalizedContent,
        ArchiveDimensionContent $localizedContent,
        array $data,
    ): void {
        if (!\array_key_exists('image', $data)) {
            return;
        }

        $imageId = $data['image'];

        if (\is_array($imageId) && isset($imageId['id'])) {
            $imageId = $imageId['id'];
        }

        $image = null;
        if ($imageId) {
            $image = $this->entityManager->getReference(Media::class, $imageId);
        }

        $unlocalizedContent->setImage($image);
        $localizedContent->setImage($image);
    }

    private function mapDocument(
        ArchiveDimensionContent $unlocalizedContent,
        ArchiveDimensionContent $localizedContent,
        array $data,
    ): void {
        if (!\array_key_exists('document', $data)) {
            return;
        }

        $documentId = $data['document'];

        if (\is_array($documentId) && isset($documentId['id'])) {
            $documentId = $documentId['id'];
        }

        $document = null;
        if ($documentId) {
            $document = $this->entityManager->getReference(Media::class, $documentId);
        }

        $unlocalizedContent->setDocument($document);
        $localizedContent->setDocument($document);
    }

    private function mapAuthor(ArchiveDimensionContent $localizedContent, array $data): void
    {
        if (\array_key_exists('author', $data)) {
            $authorId = $data['author'];
            if (\is_array($authorId) && isset($authorId['id'])) {
                $authorId = $authorId['id'];
            }
            $author = $authorId ? $this->entityManager->getReference(ContactInterface::class, $authorId) : null;
            $localizedContent->setAuthor($author);
        }

        if (\array_key_exists('authored', $data)) {
            $authored = $data['authored'] ? new \DateTimeImmutable($data['authored']) : new \DateTimeImmutable();
            $localizedContent->setAuthored($authored);
        }
    }

    private function mapShowFlags(
        ArchiveDimensionContent $unlocalizedContent,
        ArchiveDimensionContent $localizedContent,
        array $data,
    ): void {
        if (\array_key_exists('showAuthor', $data)) {
            $showAuthor = (bool) $data['showAuthor'];
            $unlocalizedContent->setShowAuthor($showAuthor);
            $localizedContent->setShowAuthor($showAuthor);
        }

        if (\array_key_exists('showDate', $data)) {
            $showDate = (bool) $data['showDate'];
            $unlocalizedContent->setShowDate($showDate);
            $localizedContent->setShowDate($showDate);
        }
    }
}
