<?php

declare(strict_types=1);

namespace Manuxi\SuluArchiveBundle\Controller\Website;

use Manuxi\SuluArchiveBundle\Entity\Archive;
use Manuxi\SuluArchiveBundle\Entity\ArchiveDimensionContent;
use Sulu\Bundle\PreviewBundle\Preview\Preview;
use Sulu\Bundle\WebsiteBundle\Resolver\TemplateAttributeResolverInterface;
use Sulu\Component\Webspace\Manager\WebspaceManagerInterface;
use Sulu\Content\Domain\Model\DimensionContentInterface;
use Sulu\Route\Domain\Repository\RouteRepositoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException;
use Twig\Environment;

class ArchiveController
{
    public function __construct(
        private readonly Environment $twig,
        private readonly TemplateAttributeResolverInterface $templateAttributeResolver,
        private readonly RouteRepositoryInterface $routeRepository,
        private readonly WebspaceManagerInterface $webspaceManager,
        private readonly RequestStack $requestStack,
    ) {
    }

    public function indexAction(
        ArchiveDimensionContent $object,
        string $view = '@SuluArchive/archive',
        bool $preview = false,
        bool $partial = false,
    ): Response {
        $request = $this->requestStack->getCurrentRequest();
        $locale = $request ? $request->getLocale() : 'en';

        $stage = $preview ? DimensionContentInterface::STAGE_DRAFT : DimensionContentInterface::STAGE_LIVE;

        $content = $object;
        $archive = $content->getResource();

        if (!$content || !$content->getTitle()) {
            $content = $this->findDimensionContentInCollection($archive, $locale, $stage);
        }

        if (!$content) {
            throw new NotAcceptableHttpException(sprintf('No content found for locale "%s".', $locale));
        }

        $parameters = $this->templateAttributeResolver->resolve([
            'archive' => $content,
            'localizations' => $this->getLocalizationsArrayForEntity($archive),
        ]);

        $viewTemplate = $view . '.html.twig';

        if (!$this->twig->getLoader()->exists($viewTemplate)) {
            throw new NotAcceptableHttpException(\sprintf('Template "%s" does not exist.', $viewTemplate));
        }

        if ($partial) {
            $twigTemplate = $this->twig->load($viewTemplate);
            $content = $twigTemplate->renderBlock('content', $this->twig->mergeGlobals($parameters));
        } elseif ($preview) {
            $parameters['previewParentTemplate'] = $viewTemplate;
            $parameters['previewContentReplacer'] = Preview::CONTENT_REPLACER;
            $content = $this->twig->render('@SuluWebsite/Preview/preview.html.twig', $parameters);
        } else {
            $content = $this->twig->render($viewTemplate, $parameters);
        }

        return new Response($content);
    }

    /**
     * Fallback method to find DimensionContent in the Archive's collection.
     */
    private function findDimensionContentInCollection(Archive $archive, string $locale, string $stage): ?ArchiveDimensionContent
    {
        foreach ($archive->getDimensionContents() as $dimensionContent) {
            if ($dimensionContent->getLocale() === $locale && $dimensionContent->getStage() === $stage) {
                return $dimensionContent;
            }
        }

        // Try draft stage if live not found
        if ($stage === DimensionContentInterface::STAGE_LIVE) {
            foreach ($archive->getDimensionContents() as $dimensionContent) {
                if ($dimensionContent->getLocale() === $locale && $dimensionContent->getStage() === DimensionContentInterface::STAGE_DRAFT) {
                    return $dimensionContent;
                }
            }
        }

        return null;
    }

    protected function getLocalizationsArrayForEntity(Archive $archive): array
    {
        $routes = $this->routeRepository->findBy([
            'resourceKey' => Archive::RESOURCE_KEY,
            'resourceId' => (string) $archive->getId(),
        ]);

        $localizations = [];
        foreach ($routes as $route) {
            $url = $this->webspaceManager->findUrlByResourceLocator(
                $route->getSlug(),
                null,
                $route->getLocale()
            );

            $localizations[$route->getLocale()] = ['locale' => $route->getLocale(), 'url' => $url];
        }

        return $localizations;
    }
}
