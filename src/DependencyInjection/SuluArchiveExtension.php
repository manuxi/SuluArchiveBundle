<?php

declare(strict_types=1);

namespace Manuxi\SuluArchiveBundle\DependencyInjection;

use Exception;
use Manuxi\SuluArchiveBundle\Admin\ArchiveAdmin;
use Manuxi\SuluArchiveBundle\Entity\Archive;
use Manuxi\SuluArchiveBundle\Entity\Location;
use Sulu\Bundle\PersistenceBundle\DependencyInjection\PersistenceExtensionTrait;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class SuluArchiveExtension extends Extension implements PrependExtensionInterface
{
    use PersistenceExtensionTrait;

    /**
     * @param array $configs
     * @param ContainerBuilder $container
     * @throws Exception
     */
    public function load(array $configs, ContainerBuilder $container)
    {

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.xml');
        $loader->load('controller.xml');

        if ($container->hasParameter('kernel.bundles')) {
            // TODO FIXME add test here
            // @codeCoverageIgnoreStart
            /** @var string[] $bundles */
            $bundles = $container->getParameter('kernel.bundles');

            if (\array_key_exists('SuluAutomationBundle', $bundles)) {
                $loader->load('automation.xml');
            }
            // @codeCoverageIgnoreEnd
        }

        $this->configurePersistence($config['objects'], $container);
    }

    public function prepend(ContainerBuilder $container): void
    {
        if ($container->hasExtension('sulu_search')) {
            $container->prependExtensionConfig(
                'sulu_search',
                [
                    'indexes' => [
                        'archive' => [
                            'name' => 'sulu_archive.search_name',
                            'icon' => 'su-archive',
                            'view' => [
                                'name' => ArchiveAdmin::EDIT_FORM_VIEW,
                                'result_to_view' => [
                                    'id' => 'id',
                                    'locale' => 'locale',
                                ],
                            ],
                            'security_context' => Archive::SECURITY_CONTEXT,
                        ],
                    ],
                ]
            );
        }

        if ($container->hasExtension('sulu_route')) {
            $container->prependExtensionConfig(
                'sulu_route',
                [
                    'mappings' => [
                        Archive::class => [
                            'generator' => 'schema',
                            'options' => [
                                //@TODO: works not yet as expected, does not translate correctly
                                //see https://github.com/sulu/sulu/pull/5920
                                'route_schema' => '/{translator.trans("sulu_archive.archive")}/{implode("-", object)}'
                            ],
                            'resource_key' => Archive::RESOURCE_KEY,
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
                    'resources' => [
                        'archive' => [
                            'routes' => [
                                'list' => 'sulu_archive.get_archive',
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
                                        'id' => 'id'
                                    ]
                                ],
                                'types' => [
                                    'list_overlay' => [
                                        'adapter' => 'table',
                                        'list_key' => Archive::LIST_KEY,
                                        'display_properties' => [
                                            'title'
                                        ],
                                        'icon' => 'su-archive',
                                        'label' => 'sulu_archive.archive_selection_label',
                                        'overlay_title' => 'sulu_archive.select_archive'
                                    ]
                                ]
                            ]
                        ],
                        'single_selection' => [
                            'single_archive_selection' => [
                                'default_type' => 'list_overlay',
                                'resource_key' => Archive::RESOURCE_KEY,
                                'view' => [
                                    'name' => ArchiveAdmin::EDIT_FORM_VIEW,
                                    'result_to_view' => [
                                        'id' => 'id'
                                    ]
                                ],
                                'types' => [
                                    'list_overlay' => [
                                        'adapter' => 'table',
                                        'list_key' => Archive::LIST_KEY,
                                        'display_properties' => [
                                            'title'
                                        ],
                                        'icon' => 'su-archive',
                                        'empty_text' => 'sulu_archive.no_archive_selected',
                                        'overlay_title' => 'sulu_archive.select_archive'
                                    ],
                                    'auto_complete' => [
                                        'display_property' => 'title',
                                        'search_properties' => [
                                            'title'
                                        ]
                                    ]
                                ]
                            ],
                        ]
                    ],
                ]
            );
        }

        $container->loadFromExtension('framework', [
            'default_locale' => 'en',
            'translator' => ['paths' => [__DIR__ . '/../Resources/config/translations/']],
        ]);
    }
}
