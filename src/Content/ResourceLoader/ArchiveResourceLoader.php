<?php

declare(strict_types=1);

namespace Manuxi\SuluArchiveBundle\Content\ResourceLoader;

use Manuxi\SuluArchiveBundle\Entity\Archive;
use Manuxi\SuluArchiveBundle\Repository\ArchiveRepository;
use Sulu\Content\Application\ResourceLoader\Loader\ResourceLoaderInterface;
use Sulu\Content\Domain\Model\DimensionContentInterface;

class ArchiveResourceLoader implements ResourceLoaderInterface
{
    public const RESOURCE_LOADER_KEY = 'archives';

    public function __construct(
        private ArchiveRepository $archiveRepository,
    ) {
    }

    /**
     * @param string[] $ids
     * @param array<string, mixed> $params
     * @return array<string, Archive>
     */
    public function load(array $ids, ?string $locale, array $params = []): array
    {
        if (empty($ids)) {
            return [];
        }

        $stage = $params['stage'] ?? DimensionContentInterface::STAGE_LIVE;
        $result = $this->archiveRepository->findByUuids($ids, $locale, $stage);

        $mappedResult = [];
        foreach ($result as $archive) {
            $mappedResult[$archive->getUuid()] = $archive;
        }

        return $mappedResult;
    }

    public static function getKey(): string
    {
        return self::RESOURCE_LOADER_KEY;
    }

    public static function getResourceKey(): string
    {
        return Archive::RESOURCE_KEY;
    }
}
