<?php

declare(strict_types=1);

namespace Manuxi\SuluArchiveBundle\DependencyInjection;

use Manuxi\SuluArchiveBundle\Entity\Archive;
use Manuxi\SuluArchiveBundle\Entity\ArchiveDimensionContent;
use Manuxi\SuluArchiveBundle\Repository\ArchiveDimensionContentRepository;
use Manuxi\SuluArchiveBundle\Repository\ArchiveRepository;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('sulu_archive');
        $root = $treeBuilder->getRootNode();

        $root
            ->children()
            ->arrayNode('routing')
            ->addDefaultsIfNotSet()
            ->children()
            ->scalarNode('route_schema')
            ->defaultValue('/{parent}/{object.getTitle()}')
            ->end()
            ->end()
            ->end()

            ->arrayNode('types')
            ->useAttributeAsKey('key')
            ->arrayPrototype()
            ->children()
            ->scalarNode('name')->isRequired()->end()
            ->scalarNode('color')->isRequired()->end()
            ->end()
            ->end()
            ->defaultValue([
                'default' => [
                    'name' => 'sulu_archive.types.default',
                    'color' => '#cccccc',
                ],
            ])
            ->end()

            ->scalarNode('default_type')
            ->defaultValue('default')
            ->end()

            ->arrayNode('objects')
            ->addDefaultsIfNotSet()
            ->children()
            ->arrayNode('archive')
            ->addDefaultsIfNotSet()
            ->children()
            ->scalarNode('model')->defaultValue(Archive::class)->end()
            ->scalarNode('repository')->defaultValue(ArchiveRepository::class)->end()
            ->end()
            ->end()
            ->arrayNode('archive_dimension_content')
            ->addDefaultsIfNotSet()
            ->children()
            ->scalarNode('model')->defaultValue(ArchiveDimensionContent::class)->end()
            ->scalarNode('repository')->defaultValue(ArchiveDimensionContentRepository::class)->end()
            ->end()
            ->end()
            ->end()
            ->end()
            ->end();

        return $treeBuilder;
    }
}
