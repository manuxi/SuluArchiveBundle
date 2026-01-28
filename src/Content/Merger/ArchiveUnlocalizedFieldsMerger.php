<?php

declare(strict_types=1);

namespace Manuxi\SuluArchiveBundle\Content\Merger;

use Manuxi\SuluArchiveBundle\Entity\ArchiveDimensionContent;
use Sulu\Content\Application\ContentMerger\Merger\MergerInterface;

/**
 * Merges unlocalized fields from source to target ArchiveDimensionContent.
 * These fields are shared across all locales.
 */
class ArchiveUnlocalizedFieldsMerger implements MergerInterface
{
    public function merge(object $targetObject, object $sourceObject): void
    {
        if (!$targetObject instanceof ArchiveDimensionContent) {
            return;
        }
        if (!$sourceObject instanceof ArchiveDimensionContent) {
            return;
        }

        // Type is unlocalized - same across all locales
        if (null !== $sourceObject->getType()) {
            $targetObject->setType($sourceObject->getType());
        }

        // Main image is unlocalized
        if (null !== $sourceObject->getImage()) {
            $targetObject->setImage($sourceObject->getImage());
        }

        // Document is unlocalized
        if (null !== $sourceObject->getDocument()) {
            $targetObject->setDocument($sourceObject->getDocument());
        }

        // Display settings are unlocalized
        $showAuthor = $sourceObject->getShowAuthor();
        if (null !== $showAuthor) {
            $targetObject->setShowAuthor($showAuthor);
        }

        $showDate = $sourceObject->getShowDate();
        if (null !== $showDate) {
            $targetObject->setShowDate($showDate);
        }
    }
}
