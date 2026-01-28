<?php

declare(strict_types=1);

namespace Manuxi\SuluArchiveBundle\Sitemap;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Manuxi\SuluArchiveBundle\Entity\Archive;
use Sulu\Bundle\WebsiteBundle\Sitemap\Sitemap;
use Sulu\Bundle\WebsiteBundle\Sitemap\SitemapAlternateLink;
use Sulu\Bundle\WebsiteBundle\Sitemap\SitemapProviderInterface;
use Sulu\Bundle\WebsiteBundle\Sitemap\SitemapUrl;
use Sulu\Component\Webspace\Manager\WebspaceManagerInterface;
use Sulu\Content\Domain\Model\DimensionContentInterface;

class ArchiveSitemapProvider implements SitemapProviderInterface
{
    public const PAGE_SIZE = 10000;

    private EntityRepository $entityRepository;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private WebspaceManagerInterface $webspaceManager,
        private string $environment,
    ) {
        $this->entityRepository = $this->entityManager->getRepository(Archive::class);
    }

    public function build($page, $scheme, $host): array
    {
        $locale = $this->getLocaleFromHost($host);

        if (!$locale) {
            return [];
        }

        $offset = ($page - 1) * self::PAGE_SIZE;
        $archives = $this->findArchives($locale, self::PAGE_SIZE, $offset);

        $alternateRoutes = $this->getAlternateRoutes($locale);

        $result = [];
        foreach ($archives as $archiveData) {
            $archiveId = (string) $archiveData['uuid'];
            $archiveLocale = $archiveData['locale'];
            $slug = $archiveData['slug'];
            $lastModified = $archiveData['lastModified'];

            if (empty($slug)) {
                continue;
            }

            $sitemapUrl = new SitemapUrl(
                $scheme . '://' . $host . $slug,
                $archiveLocale,
                $archiveLocale,
                $lastModified,
            );

            if (isset($alternateRoutes[$archiveId])) {
                foreach ($alternateRoutes[$archiveId] as $alternateLocale => $alternateSlug) {
                    if ($alternateLocale !== $archiveLocale && !empty($alternateSlug)) {
                        $sitemapUrl->addAlternateLink(
                            new SitemapAlternateLink(
                                $scheme . '://' . $host . $alternateSlug,
                                $alternateLocale,
                            )
                        );
                    }
                }
            }

            $result[] = $sitemapUrl;
        }

        return $result;
    }

    public function createSitemap($scheme, $host): Sitemap
    {
        return new Sitemap(
            $this->getAlias(),
            $this->getMaxPage($scheme, $host)
        );
    }

    public function getAlias(): string
    {
        return 'archives';
    }

    public function getMaxPage($scheme, $host): int
    {
        $locale = $this->getLocaleFromHost($host);

        if (!$locale) {
            return 0;
        }

        $count = $this->countArchives($locale);

        return (int) ceil($count / self::PAGE_SIZE);
    }

    private function getLocaleFromHost(string $host): ?string
    {
        $portalInformations = $this->webspaceManager->findPortalInformationsByHostIncludingSubdomains(
            $host,
            $this->environment
        );

        if (0 === \count($portalInformations)) {
            return null;
        }

        return \reset($portalInformations)->getLocale();
    }

    /**
     * @return array<array{uuid: string, locale: string, slug: string, lastModified: \DateTimeInterface|null}>
     */
    private function findArchives(string $locale, int $limit, int $offset): array
    {
        $queryBuilder = $this->entityRepository->createQueryBuilder('archive');

        $queryBuilder->leftJoin(
            'archive.dimensionContents',
            'dimensionContent',
            'WITH',
            'dimensionContent.locale = :locale
             AND dimensionContent.stage = :stage
             AND dimensionContent.version = :version
             AND (dimensionContent.seoHideInSitemap = :hide OR dimensionContent.seoHideInSitemap IS NULL)'
        );

        $queryBuilder->leftJoin('dimensionContent.route', 'route');

        $queryBuilder->setParameter('locale', $locale);
        $queryBuilder->setParameter('stage', DimensionContentInterface::STAGE_LIVE);
        $queryBuilder->setParameter('version', DimensionContentInterface::CURRENT_VERSION);
        $queryBuilder->setParameter('hide', false);

        $queryBuilder->andWhere('dimensionContent.id IS NOT NULL');

        $queryBuilder->select([
            'archive.uuid AS uuid',
            'dimensionContent.locale AS locale',
            'route.slug AS slug',
            'dimensionContent.changed AS lastModified',
        ]);

        $queryBuilder->orderBy('route.slug', 'ASC');
        $queryBuilder->setFirstResult($offset);
        $queryBuilder->setMaxResults($limit);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @return array<string, array<string, string>>
     */
    private function getAlternateRoutes(string $currentLocale): array
    {
        $queryBuilder = $this->entityRepository->createQueryBuilder('archive');

        $queryBuilder->leftJoin(
            'archive.dimensionContents',
            'dimensionContent',
            'WITH',
            'dimensionContent.locale IS NOT NULL
              AND dimensionContent.stage = :stage
              AND dimensionContent.version = :version
              AND (dimensionContent.seoHideInSitemap = :hide OR dimensionContent.seoHideInSitemap IS NULL)'
        );

        $queryBuilder->leftJoin('dimensionContent.route', 'route');

        $queryBuilder->setParameter('stage', DimensionContentInterface::STAGE_LIVE);
        $queryBuilder->setParameter('version', DimensionContentInterface::CURRENT_VERSION);
        $queryBuilder->setParameter('hide', false);

        $queryBuilder->andWhere('route.slug IS NOT NULL');

        $queryBuilder->select([
            'archive.uuid AS uuid',
            'dimensionContent.locale AS locale',
            'route.slug AS slug',
        ]);

        $result = [];
        foreach ($queryBuilder->getQuery()->getResult() as $row) {
            $archiveId = (string) $row['uuid'];
            $rowLocale = $row['locale'];
            $slug = $row['slug'];

            if (!isset($result[$archiveId])) {
                $result[$archiveId] = [];
            }

            $result[$archiveId][$rowLocale] = $slug;
        }

        return $result;
    }

    private function countArchives(string $locale): int
    {
        $queryBuilder = $this->entityRepository->createQueryBuilder('archive');

        $queryBuilder->select('COUNT(DISTINCT archive.uuid)');

        $queryBuilder->leftJoin(
            'archive.dimensionContents',
            'dimensionContent',
            'WITH',
            'dimensionContent.locale = :locale
             AND dimensionContent.stage = :stage
             AND dimensionContent.version = :version
             AND (dimensionContent.seoHideInSitemap = :hide OR dimensionContent.seoHideInSitemap IS NULL)'
        );

        $queryBuilder->setParameter('locale', $locale);
        $queryBuilder->setParameter('stage', DimensionContentInterface::STAGE_LIVE);
        $queryBuilder->setParameter('version', DimensionContentInterface::CURRENT_VERSION);
        $queryBuilder->setParameter('hide', false);

        $queryBuilder->andWhere('dimensionContent.id IS NOT NULL');

        return (int) $queryBuilder->getQuery()->getSingleScalarResult();
    }
}
