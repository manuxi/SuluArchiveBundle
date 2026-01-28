<?php

declare(strict_types=1);

namespace Manuxi\SuluArchiveBundle\ListBuilder;

use Manuxi\SuluArchiveBundle\Repository\ArchiveDimensionContentRepository;
use Manuxi\SuluArchiveBundle\Service\ArchiveTypeSelect;
use Sulu\Bundle\MediaBundle\Media\Manager\MediaManagerInterface;
use Sulu\Component\Rest\ListBuilder\Doctrine\DoctrineListBuilderFactory;
use Sulu\Component\Rest\ListBuilder\Doctrine\FieldDescriptor\DoctrineFieldDescriptor;
use Sulu\Component\Rest\ListBuilder\ListRestHelperInterface;
use Sulu\Component\Rest\ListBuilder\Metadata\FieldDescriptorFactoryInterface;
use Sulu\Component\Rest\ListBuilder\PaginatedRepresentation;
use Sulu\Component\Rest\RestHelperInterface;
use Sulu\Component\Webspace\Manager\WebspaceManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class DoctrineListRepresentationFactory
{
    public function __construct(
        private RestHelperInterface $restHelper,
        private ListRestHelperInterface $listRestHelper,
        private DoctrineListBuilderFactory $listBuilderFactory,
        private FieldDescriptorFactoryInterface $fieldDescriptorFactory,
        private WebspaceManagerInterface $webspaceManager,
        private ArchiveDimensionContentRepository $archiveDimensionContentRepository,
        private MediaManagerInterface $mediaManager,
        private ArchiveTypeSelect $archiveTypeSelect,
        private TranslatorInterface $translator,
    ) {
    }

    public function createDoctrineListRepresentation(
        string $resourceKey,
        array $filters = [],
        array $parameters = [],
        ?string $listKey = null,
    ): PaginatedRepresentation {
        $listKey = $listKey ?? $resourceKey;

        /** @var DoctrineFieldDescriptor[] $fieldDescriptors */
        $fieldDescriptors = $this->fieldDescriptorFactory->getFieldDescriptors($listKey);
        $listBuilder = $this->listBuilderFactory->create($fieldDescriptors['id']->getEntityName());
        $listBuilder->setIdField($fieldDescriptors['id']);
        $listBuilder->distinct(true);
        $this->restHelper->initializeListBuilder($listBuilder, $fieldDescriptors);

        if (isset($fieldDescriptors['image'])) {
            $listBuilder->addSelectField($fieldDescriptors['image']);
        }

        if (isset($fieldDescriptors['publishedState'])) {
            $listBuilder->addSelectField($fieldDescriptors['publishedState']);
        }
        if (isset($fieldDescriptors['published'])) {
            $listBuilder->addSelectField($fieldDescriptors['published']);
        }
        if (isset($fieldDescriptors['livePublished'])) {
            $listBuilder->addSelectField($fieldDescriptors['livePublished']);
        }

        foreach ($parameters as $key => $value) {
            $listBuilder->setParameter($key, $value);
        }

        foreach ($filters as $key => $value) {
            $listBuilder->where($fieldDescriptors[$key], $value);
        }

        if (isset($fieldDescriptors['version'])) {
            $listBuilder->where($fieldDescriptors['version'], 0);
        }

        $listBuilder->addGroupBy($fieldDescriptors['id']);
        $list = $listBuilder->execute();

        // sort the items to reflect the order of the given ids if the list was requested to include specific ids
        $requestedIds = $this->listRestHelper->getIds();
        if (null !== $requestedIds) {
            $idPositions = array_flip($requestedIds);

            usort($list, function ($a, $b) use ($idPositions) {
                return $idPositions[$a['id']] - $idPositions[$b['id']];
            });
        }

        $locale = $parameters['locale'] ?? null;

        $list = $this->addGhostLocaleToListElements($list, $locale);
        $list = $this->addImagesToListElements($list, $locale);
        $list = $this->addColorsToListElements($list);
        $list = $this->addPublishStateToListElements($list, $listKey);

        return new PaginatedRepresentation(
            $list,
            $resourceKey,
            (int) $listBuilder->getCurrentPage(),
            (int) $listBuilder->getLimit(),
            (int) $listBuilder->count()
        );
    }

    private function addImagesToListElements(array $listeElements, ?string $locale): array
    {
        $ids = array_filter(array_column($listeElements, 'image'));
        $images = $this->mediaManager->getFormatUrls($ids, $locale);
        foreach ($listeElements as $key => $element) {
            if (
                \array_key_exists('image', $element)
                && $element['image']
                && \array_key_exists($element['image'], $images)
            ) {
                $listeElements[$key]['image'] = $images[$element['image']];
            }
        }

        return $listeElements;
    }

    private function addGhostLocaleToListElements(array $listeElements, ?string $currentLocale): array
    {
        $availableLocales = $locales = $this->webspaceManager->getAllLocales();
        $localesCount = count($availableLocales);
        if (($key = array_search($currentLocale, $locales)) !== false) {
            unset($locales[$key]);
        }

        $ids = array_filter(array_column($listeElements, 'id'));

        foreach ($locales as $locale) {
            $missingLocales = $this->archiveDimensionContentRepository->findMissingLocaleByIds($ids, $locale, $localesCount);
            foreach ($missingLocales as $missingLocale) {
                foreach ($listeElements as $key => $element) {
                    if ($element['id'] === $missingLocale['archive'] && !array_key_exists('ghostLocale', $element)) {
                        $listeElements[$key]['ghostLocale'] = $locale;
                    }
                }
            }
        }

        return $listeElements;
    }

    /**
     * Adds types for TypeColorFieldTransformer
     */
    private function addColorsToListElements(array $listeElements): array
    {
        foreach ($listeElements as $key => $element) {
            $type = $element['type'] ?? 'default';
            $listeElements[$key]['typeColor'] = $this->archiveTypeSelect->getColor($type);
            $typeName = $this->archiveTypeSelect->getTypeName($type);
            $listeElements[$key]['typeName'] = $typeName;
            $listeElements[$key]['typeRaw'] = $type;
            // Overwrite 'type' with the translated name for display
            $listeElements[$key]['type'] = $typeName;
        }

        return $listeElements;
    }

    private function addPublishStateToListElements(array $listElements, ?string $listKey = null): array
    {
        foreach ($listElements as $key => $element) {
            if ('archives_published' === $listKey) {
                $listElements[$key]['publishedState'] = true;
                continue;
            }

            if (empty($element['published']) && !empty($element['livePublished'])) {
                $listElements[$key]['published'] = $element['livePublished'];
            }

            $workflowPlace = $element['publishedState'] ?? $element['workflowPlace'] ?? null;
            $listElements[$key]['publishedState'] = 'published' === $workflowPlace;
        }

        return $listElements;
    }
}
