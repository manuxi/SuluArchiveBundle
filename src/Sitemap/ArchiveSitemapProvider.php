<?php

declare(strict_types=1);

namespace Manuxi\SuluArchiveBundle\Sitemap;

use Manuxi\SuluArchiveBundle\Repository\ArchiveRepository;
use Sulu\Bundle\WebsiteBundle\Sitemap\Sitemap;
use Sulu\Bundle\WebsiteBundle\Sitemap\SitemapProviderInterface;
use Sulu\Bundle\WebsiteBundle\Sitemap\SitemapUrl;
use Sulu\Component\Webspace\Manager\WebspaceManagerInterface;

class ArchiveSitemapProvider implements SitemapProviderInterface
{
    private ArchiveRepository $repository;
    private WebspaceManagerInterface $webspaceManager;
    private array $locales = [];

    public function __construct(
        ArchiveRepository $repository,
        WebspaceManagerInterface $webspaceManager
    ) {
        $this->repository = $repository;
        $this->webspaceManager = $webspaceManager;
    }

    public function build($page, $scheme, $host): array
    {
        $locale = $this->getLocaleByHost($host);

        $result = [];
        foreach ($this->findArchive($locale, self::PAGE_SIZE, ($page - 1) * self::PAGE_SIZE) as $entity) {
            $result[] = new SitemapUrl(
                $scheme . '://' . $host . $entity->getRoutePath(),
                $entity->getLocale(),
                $entity->getLocale(),
                $entity->getChanged()
            );
        }

        return $result;
    }

    public function createSitemap($scheme, $host): Sitemap
    {
        return new Sitemap($this->getAlias(), $this->getMaxPage($scheme, $host));
    }

    public function getAlias(): string
    {
        return 'archive';
    }

    public function getMaxPage($scheme, $host): ?float
    {
        $locale = $this->getLocaleByHost($host);
        return ceil($this->repository->countForSitemap($locale) / self::PAGE_SIZE);
    }

    private function getLocaleByHost($host): string
    {
        if(!\array_key_exists($host, $this->locales)) {
            $portalInformation = $this->webspaceManager->getPortalInformations();

            foreach ($portalInformation as $hostName => $portal) {
                if($hostName === $host || $portal->getHost() === $host) {
                    $this->locales[$host] = $portal->getLocale();
                }
            }
        }
        return $this->locales[$host];
    }

    private function findArchive(string $locale, int $limit = null, int $offset = null): array
    {
        return $this->repository->findAllForSitemap($locale, $limit, $offset);
    }
}
