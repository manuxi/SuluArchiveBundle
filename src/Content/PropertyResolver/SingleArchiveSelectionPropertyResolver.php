<?php

declare(strict_types=1);

namespace Manuxi\SuluArchiveBundle\Content\PropertyResolver;

use Manuxi\SuluArchiveBundle\Content\ResourceLoader\ArchiveResourceLoader;
use Manuxi\SuluArchiveBundle\Entity\Archive;
use Sulu\Content\Application\ContentResolver\Value\ContentView;
use Sulu\Content\Application\PropertyResolver\Resolver\PropertyResolverInterface;

class SingleArchiveSelectionPropertyResolver implements PropertyResolverInterface
{
    public function resolve(mixed $data, string $locale, array $params = []): ContentView
    {
        if (null === $data || !\is_string($data)) {
            return ContentView::create(null, ['id' => null, ...$params]);
        }

        return ContentView::createResolvableWithReferences(
            id: $data,
            resourceLoaderKey: ArchiveResourceLoader::getKey(),
            resourceKey: Archive::RESOURCE_KEY,
            view: ['id' => $data, ...$params],
            priority: 150,
            metadata: ['properties' => $params['properties'] ?? null]
        );
    }

    public static function getType(): string
    {
        return 'single_archive_selection';
    }
}
