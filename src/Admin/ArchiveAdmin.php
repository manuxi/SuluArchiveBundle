<?php

declare(strict_types=1);

namespace Manuxi\SuluArchiveBundle\Admin;

use Manuxi\SuluArchiveBundle\Entity\Archive;
use Manuxi\SuluArchiveBundle\Service\ArchiveTypeSelect;
use Sulu\Bundle\ActivityBundle\Infrastructure\Sulu\Admin\ActivityAdmin;
use Sulu\Bundle\AdminBundle\Admin\Admin;
use Sulu\Bundle\AdminBundle\Admin\Navigation\NavigationItem;
use Sulu\Bundle\AdminBundle\Admin\Navigation\NavigationItemCollection;
use Sulu\Bundle\AdminBundle\Admin\View\DropdownToolbarAction;
use Sulu\Bundle\AdminBundle\Admin\View\TogglerToolbarAction;
use Sulu\Bundle\AdminBundle\Admin\View\ToolbarAction;
use Sulu\Bundle\AdminBundle\Admin\View\ViewBuilderFactoryInterface;
use Sulu\Bundle\AdminBundle\Admin\View\ViewCollection;
use Sulu\Bundle\AutomationBundle\Admin\AutomationAdmin;
use Sulu\Bundle\AutomationBundle\Admin\View\AutomationViewBuilderFactoryInterface;
use Sulu\Component\Security\Authorization\PermissionTypes;
use Sulu\Component\Security\Authorization\SecurityCheckerInterface;
use Sulu\Component\Webspace\Manager\WebspaceManagerInterface;

class ArchiveAdmin extends Admin
{
    public const NAV_ITEM = 'sulu_archive.archive';

    public const LIST_VIEW = 'sulu_archive.archive.list';
    public const ADD_FORM_VIEW = 'sulu_archive.archive.add_form';
    public const ADD_FORM_DETAILS_VIEW = 'sulu_archive.archive.add_form.details';
    public const EDIT_FORM_VIEW = 'sulu_archive.archive.edit_form';
    public const EDIT_FORM_DETAILS_VIEW = 'sulu_archive.archive.edit_form.details';
    public const SECURITY_CONTEXT = 'sulu.modules.archive';

    //seo,excerpt, etc
    public const EDIT_FORM_VIEW_SEO = 'sulu_archive.archive.edit_form.seo';
    public const EDIT_FORM_VIEW_EXCERPT = 'sulu_archive.archive.edit_form.excerpt';
    public const EDIT_FORM_VIEW_SETTINGS = 'sulu_archive.edit_form.settings';
    public const EDIT_FORM_VIEW_AUTOMATION = 'sulu_archive.archive.edit_form.automation';
    public const EDIT_FORM_VIEW_ACTIVITY = 'sulu_archive.archive.edit_form.activity';

    private ViewBuilderFactoryInterface $viewBuilderFactory;
    private SecurityCheckerInterface $securityChecker;
    private WebspaceManagerInterface $webspaceManager;
    private ArchiveTypeSelect $archiveTypeSelect;

    private ?AutomationViewBuilderFactoryInterface $automationViewBuilderFactory;

    private ?array $types = null;

    public function __construct(
        ViewBuilderFactoryInterface $viewBuilderFactory,
        SecurityCheckerInterface $securityChecker,
        WebspaceManagerInterface $webspaceManager,
        ArchiveTypeSelect $archiveTypeSelect,
        ?AutomationViewBuilderFactoryInterface $automationViewBuilderFactory
    ) {
        $this->viewBuilderFactory = $viewBuilderFactory;
        $this->securityChecker    = $securityChecker;
        $this->webspaceManager    = $webspaceManager;
        $this->archiveTypeSelect = $archiveTypeSelect;
        $this->automationViewBuilderFactory = $automationViewBuilderFactory;
    }

    public function configureNavigationItems(NavigationItemCollection $navigationItemCollection): void
    {
        if ($this->securityChecker->hasPermission(Archive::SECURITY_CONTEXT, PermissionTypes::EDIT)) {
            $rootNavigationItem = new NavigationItem(static::NAV_ITEM);
            $rootNavigationItem->setIcon('su-archive');
            $rootNavigationItem->setPosition(31);
            $rootNavigationItem->setView(static::LIST_VIEW);

            // Configure a NavigationItem with a View
            $archiveNavigationItem = new NavigationItem(static::NAV_ITEM);
            $archiveNavigationItem->setPosition(10);
            $archiveNavigationItem->setView(static::LIST_VIEW);

            $rootNavigationItem->addChild($archiveNavigationItem);

            $navigationItemCollection->add($rootNavigationItem);
        }
    }

    public function configureViews(ViewCollection $viewCollection): void
    {
        if (!$this->securityChecker->hasPermission(Archive::SECURITY_CONTEXT, PermissionTypes::EDIT)) {
            return;
        }

        $formToolbarActions = [];
        $listToolbarActions = [];
        $previewCondition = 'nodeType == 1';

        $locales = $this->webspaceManager->getAllLocales();

        if ($this->securityChecker->hasPermission(Archive::SECURITY_CONTEXT, PermissionTypes::EDIT)) {
            $formToolbarActions[] = new ToolbarAction('sulu_admin.save');
        }

        if ($this->securityChecker->hasPermission(Archive::SECURITY_CONTEXT, PermissionTypes::ADD)) {
            $listToolbarActions[] = new ToolbarAction('sulu_admin.add');
        }

        if ($this->securityChecker->hasPermission(Archive::SECURITY_CONTEXT, PermissionTypes::DELETE)) {
            $formToolbarActions[] = new ToolbarAction('sulu_admin.delete');
            $listToolbarActions[] = new ToolbarAction('sulu_admin.delete');
        }

        if ($this->securityChecker->hasPermission(Archive::SECURITY_CONTEXT, PermissionTypes::VIEW)) {
            $listToolbarActions[] = new ToolbarAction('sulu_admin.export');
        }

        if ($this->securityChecker->hasPermission(Archive::SECURITY_CONTEXT, PermissionTypes::LIVE)) {

            $editDropdownToolbarActions = [
                new ToolbarAction('sulu_admin.publish'),
                new ToolbarAction('sulu_admin.set_unpublished'),
            ];

            if (\count($locales) > 1) {
                $editDropdownToolbarActions[] = new ToolbarAction('sulu_admin.copy_locale');
            }

            $formToolbarActions[] = new DropdownToolbarAction(
                'sulu_admin.edit',
                'su-cog',
                $editDropdownToolbarActions
            );
        }

        if ($this->securityChecker->hasPermission(Archive::SECURITY_CONTEXT, PermissionTypes::EDIT)) {
            // Configure Archive List View
            $listView = $this->viewBuilderFactory
                ->createListViewBuilder(static::LIST_VIEW, '/archive/:locale')
                ->setResourceKey(Archive::RESOURCE_KEY)
                ->setListKey(Archive::LIST_KEY)
                ->setTitle('sulu_archive.archive')
                ->addListAdapters(['table'])
                ->addLocales($locales)
                ->setDefaultLocale($locales[0])
                ->setAddView(static::ADD_FORM_VIEW)
                ->setEditView(static::EDIT_FORM_VIEW)
                ->addToolbarActions($listToolbarActions);
            $viewCollection->add($listView);

            // Configure Archive Add View
            $addFormView = $this->viewBuilderFactory
                ->createResourceTabViewBuilder(static::ADD_FORM_VIEW, '/archive/:locale/add')
                ->setResourceKey(Archive::RESOURCE_KEY)
                ->setBackView(static::LIST_VIEW)
                ->addLocales($locales);
            $viewCollection->add($addFormView);

            $addDetailsFormView = $this->viewBuilderFactory
                ->createFormViewBuilder(static::ADD_FORM_DETAILS_VIEW, '/details')
                ->setResourceKey(Archive::RESOURCE_KEY)
                ->setFormKey(Archive::FORM_KEY)
                ->setTabTitle('sulu_admin.details')
                ->setEditView(static::EDIT_FORM_VIEW)
                ->addToolbarActions($formToolbarActions)
                ->setParent(static::ADD_FORM_VIEW);
            $viewCollection->add($addDetailsFormView);

            // Configure Archive Edit View
            $editFormView = $this->viewBuilderFactory
                ->createResourceTabViewBuilder(static::EDIT_FORM_VIEW, '/archive/:locale/:id')
                ->setResourceKey(Archive::RESOURCE_KEY)
                ->setBackView(static::LIST_VIEW)
                ->setTitleProperty('title')
                ->addLocales($locales);
            $viewCollection->add($editFormView);

            $editDetailsFormView = $this->viewBuilderFactory
                ->createPreviewFormViewBuilder(static::EDIT_FORM_DETAILS_VIEW, '/details')
                ->setPreviewCondition('id != null')
                ->setResourceKey(Archive::RESOURCE_KEY)
                ->setFormKey(Archive::FORM_KEY)
                ->setTabTitle('sulu_admin.details')
                ->addToolbarActions($formToolbarActions)
                ->setParent(static::EDIT_FORM_VIEW);
            $viewCollection->add($editDetailsFormView);

            $viewCollection->add(
                $this->viewBuilderFactory
                    ->createPreviewFormViewBuilder(static::EDIT_FORM_VIEW_SEO, '/seo')
//                    ->disablePreviewWebspaceChooser()
                    ->setResourceKey(Archive::RESOURCE_KEY)
                    ->setFormKey('page_seo')
                    ->setTabTitle('sulu_page.seo')
//                    ->setTabCondition('nodeType == 1 && shadowOn == false')
                    ->addToolbarActions($formToolbarActions)
//                    ->addRouterAttributesToFormRequest($routerAttributesToFormRequest)
                    ->setPreviewCondition($previewCondition)
                    ->setTitleVisible(true)
                    ->setTabOrder(2048)
                    ->setParent(static::EDIT_FORM_VIEW)
            );
            $viewCollection->add(
                $this->viewBuilderFactory
                    ->createPreviewFormViewBuilder(static::EDIT_FORM_VIEW_EXCERPT, '/excerpt')
//                    ->disablePreviewWebspaceChooser()
                    ->setResourceKey(Archive::RESOURCE_KEY)
                    ->setFormKey('page_excerpt')
                    ->setTabTitle('sulu_page.excerpt')
//                    ->setTabCondition('(nodeType == 1 || nodeType == 4) && shadowOn == false')
                    ->addToolbarActions($formToolbarActions)
//                    ->addRouterAttributesToFormRequest($routerAttributesToFormRequest)
//                    ->addRouterAttributesToFormMetadata($routerAttributesToFormMetadata)
                    ->setPreviewCondition($previewCondition)
                    ->setTitleVisible(true)
                    ->setTabOrder(3072)
                    ->setParent(static::EDIT_FORM_VIEW)
            );
            $viewCollection->add(
                $this->viewBuilderFactory
                    ->createPreviewFormViewBuilder(static::EDIT_FORM_VIEW_SETTINGS, '/settings')
                    ->disablePreviewWebspaceChooser()
                    ->setResourceKey(Archive::RESOURCE_KEY)
                    ->setFormKey('archive_settings')
                    ->setTabTitle('sulu_page.settings')
                    ->addToolbarActions($formToolbarActions)
                    ->setPreviewCondition($previewCondition)
                    ->setTitleVisible(true)
                    ->setTabOrder(4096)
                    ->setParent(static::EDIT_FORM_VIEW)
            );

            if ($this->automationViewBuilderFactory
                && $this->securityChecker->hasPermission(AutomationAdmin::SECURITY_CONTEXT, PermissionTypes::EDIT)
            ) {
                $viewCollection->add(
                    $this->automationViewBuilderFactory
                        ->createTaskListViewBuilder(static::EDIT_FORM_VIEW_AUTOMATION,'/automation',Archive::class)
                        ->setTabOrder(5120)
                        ->setParent(static::EDIT_FORM_VIEW)
                );
            }

            /*
            if ($this->securityChecker->hasPermission(ActivityAdmin::SECURITY_CONTEXT, PermissionTypes::VIEW)) {
                $viewCollection->add(
                    $this->viewBuilderFactory
                        ->createResourceTabViewBuilder(static::EDIT_FORM_VIEW_ACTIVITY, '/activity')
                        ->setResourceKey(Archive::RESOURCE_KEY)
                        ->setTabTitle('sulu_admin.activity')
                        ->setTitleProperty('')
                        ->setTabOrder(6144)
                        ->addRouterAttributesToBlacklist(['active', 'filter', 'limit', 'page', 'search', 'sortColumn', 'sortOrder'])
                        ->setParent(static::EDIT_FORM_VIEW)
                );

                $viewCollection->add(
                    $this->viewBuilderFactory
                        ->createListViewBuilder(static::EDIT_FORM_VIEW_ACTIVITY . '.activity', '/activity')
                        ->setResourceKey(Archive::RESOURCE_KEY)
                        ->setTabTitle('sulu_admin.activity')
                        ->setTabOrder(6168)
                        ->setListKey('activities')
                        ->addListAdapters(['table'])
                        ->addAdapterOptions([
                            'table' => [
                                'skin' => 'flat',
                                'show_header' => false,
                            ],
                        ])
                        ->disableTabGap()
                        ->disableSearching()
                        ->disableSelection()
                        ->disableColumnOptions()
                        ->disableFiltering()
                        ->addResourceStorePropertiesToListRequest(['id' => 'resourceId'])
                        ->addRequestParameters(['resourceKey' => Archive::RESOURCE_KEY])
                        ->setParent(static::EDIT_FORM_VIEW_ACTIVITY)
                );
            }
            */
        }
    }

    /**
     * @return mixed[]
     */
    public function getSecurityContexts(): array
    {
        return [
            self::SULU_ADMIN_SECURITY_SYSTEM => [
                'Archive' => [
                    Archive::SECURITY_CONTEXT => [
                        PermissionTypes::VIEW,
                        PermissionTypes::ADD,
                        PermissionTypes::EDIT,
                        PermissionTypes::DELETE,
                        PermissionTypes::LIVE,
                    ],
                ],
            ],
        ];
    }

    private function getTypes(): array
    {
        if(null === $this->types) {
            $this->types = $this->archiveTypeSelect->getValues();
        }

        return $this->types;
    }

    public function getSecurityContextsTmp(): array
    {
        $securityContext = [];

        foreach ($this->getTypes() as $typeKey => $type) {
            $securityContext[static::getArchiveSecurityContext($typeKey)] = [
                PermissionTypes::VIEW,
                PermissionTypes::ADD,
                PermissionTypes::EDIT,
                PermissionTypes::DELETE,
                PermissionTypes::LIVE,
            ];
        }

        return [
            'Sulu' => [
                'Global' => [
                    Archive::SECURITY_CONTEXT => [
                        PermissionTypes::VIEW,
                        PermissionTypes::ADD,
                        PermissionTypes::EDIT,
                        PermissionTypes::DELETE,
                        PermissionTypes::LIVE,
                    ],
                ],
                'Archive types' => $securityContext,
            ],
        ];
    }

    public static function getArchiveSecurityContext(string $typeKey): string
    {
        return \sprintf('%s_%s', Archive::SECURITY_CONTEXT, $typeKey);
    }

    public function getConfigKey(): ?string
    {
        return 'sulu_archive';
    }
}
