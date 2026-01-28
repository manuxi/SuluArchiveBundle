<?php

declare(strict_types=1);

namespace Manuxi\SuluArchiveBundle\Content\PropertyResolver;

use Manuxi\SuluArchiveBundle\Content\ResourceLoader\ArchiveResourceLoader;
use Manuxi\SuluArchiveBundle\Entity\Archive;
use Sulu\Content\Application\ContentResolver\Value\ContentView;
use Sulu\Content\Application\PropertyResolver\Resolver\PropertyResolverInterface;

class ArchiveSelectionPropertyResolver implements PropertyResolverInterface
{
    public function resolve(mixed $data, string $locale, array $params = []): ContentView
    {
        if (!is_array($data) || 0 === count($data)) {
            return ContentView::create([], ['ids' => [], ...$params]);
        }

        // Convert IDs to strings for ResourceLoader
        $stringIds = array_map('strval', $data);

        return ContentView::createResolvablesWithReferences(
            ids: $stringIds,
            resourceLoaderKey: ArchiveResourceLoader::getKey(),
            resourceKey: Archive::RESOURCE_KEY,
            view: ['ids' => $data, ...$params],
            priority: 150
        );
    }

    public static function getType(): string
    {
        return 'archive_selection';
    }
}

