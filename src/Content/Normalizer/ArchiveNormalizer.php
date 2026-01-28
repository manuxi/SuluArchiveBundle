<?php

declare(strict_types=1);

namespace Manuxi\SuluArchiveBundle\Content\Normalizer;

use Manuxi\SuluArchiveBundle\Entity\Archive;
use Manuxi\SuluArchiveBundle\Entity\ArchiveDimensionContent;
use Manuxi\SuluArchiveBundle\Service\ArchiveTypeSelect;
use Sulu\Content\Application\ContentNormalizer\Normalizer\NormalizerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ArchiveNormalizer implements NormalizerInterface
{
    public function __construct(
        private TranslatorInterface $translator,
        private ArchiveTypeSelect $archiveTypeSelect,
    ) {
    }

    public function getIgnoredAttributes(object $object): array
    {
        if (!$object instanceof ArchiveDimensionContent) {
            return [];
        }

        return [
            'archive',
            'image',
            'images',
            'document',
            'author',
        ];
    }

    public function enhance(object $object, array $normalizedData): array
    {
        if (!$object instanceof ArchiveDimensionContent) {
            return $normalizedData;
        }

        /** @var Archive $archive */
        $archive = $object->getResource();

        if (!$archive) {
            return $normalizedData;
        }

        $normalizedData['id'] = $archive->getUuid();
        $normalizedData['uuid'] = $archive->getUuid();

        // Type
        $type = $object->getType() ?? 'default';
        $normalizedData['typeName'] = $this->archiveTypeSelect->getTypeName($type);
        $normalizedData['typeColor'] = $this->archiveTypeSelect->getColor($type);

        // Author
        $author = $object->getAuthor();
        if (null !== $author) {
            $normalizedData['authorId'] = $author->getId();
        }

        // Image
        $image = $object->getImage();
        if (null !== $image) {
            if (!isset($normalizedData['image']) || !\is_array($normalizedData['image'])) {
                $normalizedData['image'] = [];
            }
            $normalizedData['image']['id'] = $image->getId();
        }

        // Images
        $images = $object->getImages();
        if (empty($images)) {
            $normalizedData['images'] = ['ids' => []];
        } elseif (!isset($images['ids'])) {
            $normalizedData['images'] = ['ids' => $images];
        } else {
            $normalizedData['images'] = $images;
        }

        // Document
        $document = $object->getDocument();
        if (null !== $document) {
            if (!isset($normalizedData['document']) || !\is_array($normalizedData['document'])) {
                $normalizedData['document'] = [];
            }
            $normalizedData['document']['id'] = $document->getId();
        }

        return $normalizedData;
    }
}
