<?php

declare(strict_types=1);

namespace Manuxi\SuluArchiveBundle\Entity\Models;

use Manuxi\SuluArchiveBundle\Entity\ArchiveSeo;
use Manuxi\SuluArchiveBundle\Entity\Interfaces\ArchiveSeoModelInterface;
use Manuxi\SuluArchiveBundle\Entity\Traits\ArrayPropertyTrait;
use Manuxi\SuluArchiveBundle\Repository\ArchiveSeoRepository;
use Symfony\Component\HttpFoundation\Request;

class ArchiveSeoModel implements ArchiveSeoModelInterface
{
    use ArrayPropertyTrait;

    private ArchiveSeoRepository $archiveSeoRepository;

    public function __construct(
        ArchiveSeoRepository $archiveSeoRepository
    ) {
        $this->archiveSeoRepository = $archiveSeoRepository;
    }

    /**
     * @param ArchiveSeo $archiveSeo
     * @param Request $request
     * @return ArchiveSeo
     */
    public function updateArchiveSeo(ArchiveSeo $archiveSeo, Request $request): ArchiveSeo
    {
        $archiveSeo = $this->mapDataToArchiveSeo($archiveSeo, $request->request->all()['ext']['seo']);
        return $this->archiveSeoRepository->save($archiveSeo);
    }

    private function mapDataToArchiveSeo(ArchiveSeo $archiveSeo, array $data): ArchiveSeo
    {
        $locale = $this->getProperty($data, 'locale');
        if ($locale) {
            $archiveSeo->setLocale($locale);
        }
        $title = $this->getProperty($data, 'title');
        if ($title) {
            $archiveSeo->setTitle($title);
        }
        $description = $this->getProperty($data, 'description');
        if ($description) {
            $archiveSeo->setDescription($description);
        }
        $keywords = $this->getProperty($data, 'keywords');
        if ($keywords) {
            $archiveSeo->setKeywords($keywords);
        }
        $canonicalUrl = $this->getProperty($data, 'canonicalUrl');
        if ($canonicalUrl) {
            $archiveSeo->setCanonicalUrl($canonicalUrl);
        }
        $noIndex = $this->getProperty($data, 'noIndex');
        if ($noIndex) {
            $archiveSeo->setNoIndex($noIndex);
        }
        $noFollow = $this->getProperty($data, 'noFollow');
        if ($noFollow) {
            $archiveSeo->setNoFollow($noFollow);
        }
        $hideInSitemap = $this->getProperty($data, 'hideInSitemap');
        if ($hideInSitemap) {
            $archiveSeo->setHideInSitemap($hideInSitemap);
        }
        return $archiveSeo;
    }
}
