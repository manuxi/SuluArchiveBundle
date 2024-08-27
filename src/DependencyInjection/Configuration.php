<?php

declare(strict_types=1);

namespace Manuxi\SuluArchiveBundle\DependencyInjection;

use Manuxi\SuluArchiveBundle\Entity\Archive;
use Manuxi\SuluArchiveBundle\Entity\ArchiveExcerpt;
use Manuxi\SuluArchiveBundle\Entity\ArchiveExcerptTranslation;
use Manuxi\SuluArchiveBundle\Entity\ArchiveSeo;
use Manuxi\SuluArchiveBundle\Entity\ArchiveSeoTranslation;
use Manuxi\SuluArchiveBundle\Entity\ArchiveTranslation;
use Manuxi\SuluArchiveBundle\Repository\ArchiveExcerptRepository;
use Manuxi\SuluArchiveBundle\Repository\ArchiveExcerptTranslationRepository;
use Manuxi\SuluArchiveBundle\Repository\ArchiveRepository;
use Manuxi\SuluArchiveBundle\Repository\ArchiveSeoRepository;
use Manuxi\SuluArchiveBundle\Repository\ArchiveSeoTranslationRepository;
use Manuxi\SuluArchiveBundle\Repository\ArchiveTranslationRepository;
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
                    ->arrayNode('archive_translation')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode('model')->defaultValue(ArchiveTranslation::class)->end()
                            ->scalarNode('repository')->defaultValue(ArchiveTranslationRepository::class)->end()
                        ->end()
                    ->end()
                    ->arrayNode('archive_seo')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode('model')->defaultValue(ArchiveSeo::class)->end()
                            ->scalarNode('repository')->defaultValue(ArchiveSeoRepository::class)->end()
                        ->end()
                    ->end()
                    ->arrayNode('archive_seo_translation')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode('model')->defaultValue(ArchiveSeoTranslation::class)->end()
                            ->scalarNode('repository')->defaultValue(ArchiveSeoTranslationRepository::class)->end()
                        ->end()
                    ->end()
                    ->arrayNode('archive_excerpt')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode('model')->defaultValue(ArchiveExcerpt::class)->end()
                            ->scalarNode('repository')->defaultValue(ArchiveExcerptRepository::class)->end()
                        ->end()
                    ->end()
                    ->arrayNode('archive_excerpt_translation')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode('model')->defaultValue(ArchiveExcerptTranslation::class)->end()
                            ->scalarNode('repository')->defaultValue(ArchiveExcerptTranslationRepository::class)->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
