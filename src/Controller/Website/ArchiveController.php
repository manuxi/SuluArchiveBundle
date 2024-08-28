<?php

declare(strict_types=1);

namespace Manuxi\SuluArchiveBundle\Controller\Website;

use Exception;
use JMS\Serializer\SerializerBuilder;
use Manuxi\SuluArchiveBundle\Entity\Archive;
use Manuxi\SuluArchiveBundle\Repository\ArchiveRepository;
use Sulu\Bundle\MediaBundle\Media\Manager\MediaManagerInterface;
use Sulu\Bundle\RouteBundle\Entity\RouteRepositoryInterface;
use Sulu\Bundle\WebsiteBundle\Resolver\TemplateAttributeResolverInterface;
use Sulu\Component\Webspace\Manager\WebspaceManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

class ArchiveController extends AbstractController
{
    private TranslatorInterface $translator;
    private ArchiveRepository $repository;
    private WebspaceManagerInterface $webspaceManager;
    private TemplateAttributeResolverInterface $templateAttributeResolver;
    private RouteRepositoryInterface $routeRepository;

    public function __construct(
        RequestStack $requestStack,
        MediaManagerInterface $mediaManager,
        ArchiveRepository $repository,
        WebspaceManagerInterface $webspaceManager,
        TranslatorInterface $translator,
        TemplateAttributeResolverInterface $templateAttributeResolver,
        RouteRepositoryInterface $routeRepository
    ) {
        parent::__construct($requestStack, $mediaManager);

        $this->repository                = $repository;
        $this->webspaceManager           = $webspaceManager;
        $this->translator                = $translator;
        $this->templateAttributeResolver = $templateAttributeResolver;
        $this->routeRepository           = $routeRepository;
    }

    /**
     * @param Archive $archive
     * @param string $view
     * @param bool $preview
     * @param bool $partial
     * @return Response
     * @throws Exception
     */
    public function indexAction(Archive $archive, string $view = '@SuluArchive/archive', bool $preview = false, bool $partial = false): Response
    {

        $viewTemplate = $this->getViewTemplate($view, $this->request, $preview);

        $parameters = $this->templateAttributeResolver->resolve([
            'archive'   => $archive,
            'content' => [
                'title'    => $this->translator->trans('sulu_archive.archive'),
                'subtitle' => $archive->getTitle(),
            ],
            'path'          => $archive->getRoutePath(),
            'extension'     => $this->extractExtension($archive),
            'localizations' => $this->getLocalizationsArrayForEntity($archive),
            'created'       => $archive->getCreated(),
        ]);

        return $this->prepareResponse($viewTemplate, $parameters, $preview, $partial);
    }

    /**
     * With the help of this method the corresponding localisations for the
     * current archive is found e.g. to be linked in the language switcher.
     * @param Archive $archive
     * @return array<string, array>
     */
    protected function getLocalizationsArrayForEntity(Archive $archive): array
    {
        $routes = $this->routeRepository->findAllByEntity(Archive::class, (string)$archive->getId());

        $localizations = [];
        foreach ($routes as $route) {
            $url = $this->webspaceManager->findUrlByResourceLocator(
                $route->getPath(),
                null,
                $route->getLocale()
            );

            $localizations[$route->getLocale()] = ['locale' => $route->getLocale(), 'url' => $url];
        }

        return $localizations;
    }

    private function extractExtension(Archive $archive): array
    {
        $serializer = SerializerBuilder::create()->build();
        return $serializer->toArray($archive->getExt());
    }

    /**
     * @return string[]
     */
    public static function getSubscribedServices(): array
    {
/*        return array_merge(
            parent::getSubscribedServices(),
            [
                WebspaceManagerInterface::class,
                RouteRepositoryInterface::class,
                TemplateAttributeResolverInterface::class,
            ]
        );*/
        $subscribedServices = parent::getSubscribedServices();

        $subscribedServices['sulu_core.webspace.webspace_manager'] = WebspaceManagerInterface::class;
        $subscribedServices['sulu.repository.route'] = RouteRepositoryInterface::class;
        $subscribedServices['sulu_website.resolver.template_attribute'] = TemplateAttributeResolverInterface::class;

        return $subscribedServices;
    }

}
