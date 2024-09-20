<?php

declare(strict_types=1);

namespace Manuxi\SuluArchiveBundle\Content;

use Countable;
use Doctrine\ORM\EntityManagerInterface;
use Manuxi\SuluArchiveBundle\Admin\ArchiveAdmin;
use Manuxi\SuluArchiveBundle\Entity\Archive;
use Manuxi\SuluArchiveBundle\Service\ArchiveTypeSelect;
use Sulu\Component\Serializer\ArraySerializerInterface;
use Sulu\Component\SmartContent\Configuration\ProviderConfigurationInterface;
use Sulu\Component\SmartContent\DataProviderResult;
use Sulu\Component\SmartContent\Orm\BaseDataProvider;
use Sulu\Component\SmartContent\Orm\DataProviderRepositoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class ArchiveDataProvider extends BaseDataProvider
{
    private int $defaultLimit = 12;

    public function __construct(
        DataProviderRepositoryInterface $repository,
        ArraySerializerInterface $serializer,
        private RequestStack $requestStack,
        private EntityManagerInterface $entityManager,
        private ArchiveTypeSelect $archiveTypeSelect
    ) {
        parent::__construct($repository, $serializer);
    }

    private function getTypes(): array
    {
        $types = $this->archiveTypeSelect->getValues();
        $return = [];
        foreach ($types as $key => $values) {
            $temp = [];
            $temp['type'] = $values['name'];
            $temp['title'] = $values['title'];
            $return[] = $temp;
        }
        return $return;
    }

    private function getSorting(): array
    {
        return [
            ['column' => 'translation.title', 'title' => 'sulu_archive.title'],
            ['column' => 'translation.authored', 'title' => 'sulu_archive.authored'],
            ['column' => 'translation.published', 'title' => 'sulu_archive.published'],
            ['column' => 'translation.publishedAt', 'title' => 'sulu_archive.published_at'],
        ];
    }

    public function getConfiguration(): ProviderConfigurationInterface
    {
        if (null === $this->configuration) {
            $this->configuration = self::createConfigurationBuilder()
                ->enableLimit()
                ->enablePagination()
                ->enablePresentAs()
                ->enableCategories()
                ->enableTags()
                ->enableTypes($this->getTypes())
                ->enableSorting($this->getSorting())
                ->enableView(ArchiveAdmin::EDIT_FORM_VIEW, ['id' => 'id'])
                ->getConfiguration();
        }

        return parent::getConfiguration();
    }

    public function resolveResourceItems(
        array $filters,
        array $propertyParameter,
        array $options = [],
        $limit = null,
        $page = 1,
        $pageSize = null
    ): DataProviderResult
    {
        $locale = $options['locale'];
        $request = $this->requestStack->getCurrentRequest();
        $options['page'] = $request->get('p');
        $archive = $this->entityManager->getRepository(Archive::class)->findByFilters($filters, $page, $pageSize, $limit, $locale, $options);
        return new DataProviderResult($archive, $this->entityManager->getRepository(Archive::class)->hasNextPage($filters, $page, $pageSize, $limit, $locale, $options));
    }

    protected function decorateDataItems(array $data): array
    {
        return \array_map(
            static function ($item) {
                return new ArchiveDataItem($item);
            },
            $data
        );
    }

    /**
     * Returns flag "hasNextPage".
     * It combines the limit/query-count with the page and page-size.
     *
     * @noinspection PhpUnusedPrivateMethodInspection
     * @param Countable $queryResult
     * @param int|null $limit
     * @param int $page
     * @param int|null $pageSize
     * @return bool
     */
    private function hasNextPage(Countable $queryResult, ?int $limit, int $page, ?int $pageSize): bool
    {
        $count = $queryResult->count();

        if (null === $pageSize || $pageSize > $this->defaultLimit) {
            $pageSize = $this->defaultLimit;
        }

        $offset = ($page - 1) * $pageSize;
        if ($limit && $offset + $pageSize > $limit) {
            return false;
        }

        return $count > ($page * $pageSize);
    }

}
