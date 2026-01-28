<?php

declare(strict_types=1);

namespace Manuxi\SuluArchiveBundle\Link;

use Manuxi\SuluArchiveBundle\Entity\Archive;
use Manuxi\SuluArchiveBundle\Entity\ArchiveDimensionContent;
use Manuxi\SuluArchiveBundle\Repository\ArchiveRepository;
use Sulu\Bundle\MarkupBundle\Markup\Link\LinkConfigurationBuilder;
use Sulu\Bundle\MarkupBundle\Markup\Link\LinkItem;
use Sulu\Bundle\MarkupBundle\Markup\Link\LinkProviderInterface;
use Sulu\Content\Application\ContentAggregator\ContentAggregatorInterface;
use Sulu\Content\Domain\Model\DimensionContentInterface;
use Sulu\Content\Domain\Model\WorkflowInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class LinkProvider implements LinkProviderInterface
{
    public function __construct(
        private readonly ContentAggregatorInterface $contentAggregator,
        private ArchiveRepository $archiveRepository,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function getConfigurationBuilder(): LinkConfigurationBuilder
    {
        return LinkConfigurationBuilder::create()
            ->setTitle($this->translator->trans('sulu_archive.archives', [], 'admin'))
            ->setResourceKey(Archive::RESOURCE_KEY)
            ->setListAdapter('table')
            ->setDisplayProperties(['title'])
            ->setOverlayTitle($this->translator->trans('sulu_archive.archives', [], 'admin'))
            ->setEmptyText($this->translator->trans('sulu_archive.empty_list', [], 'admin'))
            ->setIcon('su-archive');
    }

    public function preload(array $hrefs, string $locale, bool $published = true): iterable
    {
        if (0 === \count($hrefs)) {
            return [];
        }

        $dimensionAttributes = [
            'locale' => $locale,
            'stage' => $published ? DimensionContentInterface::STAGE_LIVE : DimensionContentInterface::STAGE_DRAFT,
        ];

        $stage = $dimensionAttributes['stage'];
        $result = $this->archiveRepository->findByUuids($hrefs, $locale, $stage);

        foreach ($result as $archive) {
            /** @var ArchiveDimensionContent $dimensionContent */
            $dimensionContent = $this->contentAggregator->aggregate($archive, $dimensionAttributes);

            $title = $dimensionContent->getTitle() ?? '';
            $url = $dimensionContent->getRoute()?->getSlug() ?? '';
            $isPublished = $dimensionContent->getWorkflowPlace() === WorkflowInterface::WORKFLOW_PLACE_PUBLISHED;

            yield new LinkItem((string) $archive->getId(), $title, $url, $isPublished);
        }
    }
}
