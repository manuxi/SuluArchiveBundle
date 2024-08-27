<?php

declare(strict_types=1);

namespace Manuxi\SuluArchiveBundle\Link;

use Manuxi\SuluArchiveBundle\Entity\Archive;
use Manuxi\SuluArchiveBundle\Repository\ArchiveRepository;
use Sulu\Bundle\MarkupBundle\Markup\Link\LinkConfiguration;
use Sulu\Bundle\MarkupBundle\Markup\Link\LinkConfigurationBuilder;
use Sulu\Bundle\MarkupBundle\Markup\Link\LinkItem;
use Sulu\Bundle\MarkupBundle\Markup\Link\LinkProviderInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ArchiveLinkProvider implements LinkProviderInterface
{
    private ArchiveRepository $archiveRepository;
    private TranslatorInterface $translator;

    public function __construct(ArchiveRepository $archiveRepository, TranslatorInterface $translator)
    {
        $this->archiveRepository = $archiveRepository;
        $this->translator = $translator;
    }

    public function getConfiguration(): LinkConfiguration
    {
        return LinkConfigurationBuilder::create()
            ->setTitle($this->translator->trans('sulu_archive.archive',[],'admin'))
            ->setResourceKey(Archive::RESOURCE_KEY) // the resourceKey of the entity that should be loaded
            ->setListAdapter('table')
            ->setDisplayProperties(['title'])
            ->setOverlayTitle($this->translator->trans('sulu_archive.archive',[],'admin'))
            ->setEmptyText($this->translator->trans('sulu_archive.empty_archivelist',[],'admin'))
            ->setIcon('su-archive')
            ->getLinkConfiguration();
    }

    public function preload(array $hrefs, $locale, $published = true): array
    {
        if (0 === count($hrefs)) {
            return [];
        }

        $result = [];
        $elements = $this->archiveRepository->findBy(['id' => $hrefs]); // load items by id
        foreach ($elements as $element) {
            $element->setLocale($locale);
            $result[] = new LinkItem($element->getId(), $element->getTitle(), $element->getRoutePath(), $element->isPublished()); // create link-item foreach item
        }

        return $result;
    }
}
