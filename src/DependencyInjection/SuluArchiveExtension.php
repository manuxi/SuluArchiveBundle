<?php

declare(strict_types=1);

namespace Manuxi\SuluArchiveBundle\DependencyInjection;

use Manuxi\SuluArchiveBundle\Admin\ArchiveAdmin;
use Manuxi\SuluArchiveBundle\Entity\Archive;
use Sulu\Bundle\PersistenceBundle\DependencyInjection\PersistenceExtensionTrait;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Yaml\Yaml;

class SuluArchiveExtension extends Extension implements PrependExtensionInterface
{
    use PersistenceExtensionTrait;

    /**
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('sulu_archive.types', $config['types'] ?? []);
        $container->setParameter('sulu_archive.default_type', $config['default_type'] ?? 'default');

        $container->setParameter(
            'sulu_archive.routing.route_schema',
            $config['routing']['route_schema']
        );

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yaml');
        $loader->load('controller.yaml');

        $this->configurePersistence($config['objects'], $container);
    }

    public function prepend(ContainerBuilder $container): void
    {
        if ($container->hasExtension('doctrine')) {
            $container->prependExtensionConfig(
                'doctrine',
                [
                    'orm' => [
                        'mappings' => [
                            'SuluArchiveBundle' => [
                                'type' => 'xml',
                                'dir' => __DIR__ . '/../Resources/config/doctrine',
                                'prefix' => 'Manuxi\SuluArchiveBundle\Entity',
                                'alias' => 'SuluArchiveBundle',
                            ],
                        ],
                    ],
                ]
            );
        }

        if ($container->hasExtension('sulu_archive')) {
            $configs = $container->getExtensionConfig('sulu_archive');

            $hasProjectTypes = false;
            foreach ($configs as $config) {
                if (isset($config['types'])) {
                    $hasProjectTypes = true;
                    break;
                }
            }

            if (!$hasProjectTypes) {
                $defaultConfigFile = __DIR__ . '/../Resources/config/packages/sulu_archive.yaml';
                if (file_exists($defaultConfigFile)) {
                    $defaultConfig = Yaml::parseFile($defaultConfigFile);
                    if (isset($defaultConfig['sulu_archive'])) {
                        $container->prependExtensionConfig('sulu_archive', $defaultConfig['sulu_archive']);
                    }
                }
            }
        }

        if ($container->hasExtension('sulu_search')) {
            $container->prependExtensionConfig(
                'sulu_search',
                [
                    'admin' => [
                        'resources' => [
                            Archive::RESOURCE_KEY => [
                                'name' => 'sulu_archive.archives',
                                'icon' => 'su-archive',
                                'route' => [
                                    'name' => ArchiveAdmin::EDIT_FORM_VIEW,
                                    'resultToRoute' => [
                                        'resourceId' => 'id',
                                        'locale' => 'locale',
                                    ],
                                ],
                                'securityContext' => Archive::SECURITY_CONTEXT,
                            ],
                        ],
                    ],
                ],
            );
        }

        if ($container->hasExtension('sulu_seo')) {
            $container->prependExtensionConfig(
                'sulu_seo',
                [
                    'content' => [
                        'types' => [
                            Archive::TEMPLATE_TYPE => [
                                'template_driver' => true,
                            ],
                        ],
                    ],
                ]
            );
        }

        if ($container->hasExtension('sulu_excerpt')) {
            $container->prependExtensionConfig(
                'sulu_excerpt',
                [
                    'content' => [
                        'types' => [
                            Archive::TEMPLATE_TYPE => [
                                'template_driver' => true,
                            ],
                        ],
                    ],
                ]
            );
        }

        if ($container->hasExtension('sulu_media')) {
            $container->prependExtensionConfig(
                'sulu_media',
                [
                    'system_collections' => [
                        'sulu_archive' => [
                            'meta_title' => ['en' => 'Archive', 'de' => 'Archiv'],
                            'collections' => [
                                'archives' => [
                                    'meta_title' => ['en' => 'Archive', 'de' => 'Archiv'],
                                ],
                            ],
                        ],
                    ],
                ]
            );
        }

        if ($container->hasExtension('sulu_admin')) {
            $container->prependExtensionConfig(
                'sulu_admin',
                [
                    'lists' => [
                        'directories' => [
                            __DIR__ . '/../Resources/config/lists',
                        ],
                    ],
                    'forms' => [
                        'directories' => [
                            __DIR__ . '/../Resources/config/forms',
                        ],
                    ],
                    'templates' => [
                        Archive::TEMPLATE_TYPE => [
                            'default_type' => Archive::TEMPLATE_TYPE,
                            'directories' => [
                                'bundle' => __DIR__ . '/../Resources/config/templates/archives',
                                'app' => '%kernel.project_dir%/config/templates/archives',
                            ],
                        ],
                    ],
                    'resources' => [
                        'archives' => [
                            'routes' => [
                                'list' => 'sulu_archive.get_archives',
                                'detail' => 'sulu_archive.get_archive',
                            ],
                        ],
                        'archives_versions' => [
                            'routes' => [
                                'list' => 'sulu_archive.get_archive_versions',
                                'detail' => 'sulu_archive.get_archive',
                            ],
                        ],
                        'archive-settings' => [
                            'routes' => [
                                'detail' => 'sulu_archive.get_archive-settings',
                            ],
                        ],
                    ],
                    'field_type_options' => [
                        'selection' => [
                            'archive_selection' => [
                                'default_type' => 'list_overlay',
                                'resource_key' => Archive::RESOURCE_KEY,
                                'view' => [
                                    'name' => ArchiveAdmin::EDIT_FORM_VIEW,
                                    'result_to_view' => [
                                        'id' => 'id',
                                    ],
                                ],
                                'types' => [
                                    'list_overlay' => [
                                        'adapter' => 'table',
                                        'list_key' => Archive::LIST_KEY_PUBLISHED,
                                        'display_properties' => [
                                            'title',
                                        ],
                                        'icon' => 'su-archive',
                                        'label' => 'sulu_archive.archive_selection_label',
                                        'overlay_title' => 'sulu_archive.select_archives',
                                    ],
                                ],
                            ],
                        ],
                        'single_selection' => [
                            'single_archive_selection' => [
                                'default_type' => 'list_overlay',
                                'resource_key' => Archive::RESOURCE_KEY,
                                'view' => [
                                    'name' => ArchiveAdmin::EDIT_FORM_VIEW,
                                    'result_to_view' => [
                                        'id' => 'id',
                                    ],
                                ],
                                'types' => [
                                    'list_overlay' => [
                                        'adapter' => 'table',
                                        'list_key' => Archive::LIST_KEY_PUBLISHED,
                                        'display_properties' => [
                                            'title',
                                        ],
                                        'icon' => 'su-archive',
                                        'empty_text' => 'sulu_archive.no_archive_selected',
                                        'overlay_title' => 'sulu_archive.select_archive',
                                    ],
                                    'auto_complete' => [
                                        'display_property' => 'title',
                                        'search_properties' => [
                                            'title',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ]
            );
        }

        $container->loadFromExtension('framework', [
            'default_locale' => 'en',
            'translator' => ['paths' => [__DIR__ . '/../Resources/translations/']],
        ]);
    }
}
