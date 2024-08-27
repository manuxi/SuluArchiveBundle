<?php

declare(strict_types=1);

namespace Manuxi\SuluArchiveBundle\Routing;

use Manuxi\SuluArchiveBundle\Controller\Website\ArchiveController;
use Manuxi\SuluArchiveBundle\Entity\Archive;
use Manuxi\SuluArchiveBundle\Repository\ArchiveRepository;
use Sulu\Bundle\RouteBundle\Routing\Defaults\RouteDefaultsProviderInterface;

class ArchiveRouteDefaultsProvider implements RouteDefaultsProviderInterface
{

    private ArchiveRepository $repository;

    public function __construct(ArchiveRepository $repository) {
        $this->repository = $repository;
    }

    /**
     * @param $entityClass
     * @param $id
     * @param $locale
     * @param null $object
     * @return mixed[]
     */
    public function getByEntity($entityClass, $id, $locale, $object = null): array
    {
        return [
            '_controller' => ArchiveController::class . '::indexAction',
            //'archive' => $object ?: $this->repository->findById((int)$id, $locale),
            'archive' => $this->repository->findById((int)$id, $locale),
        ];
    }

    public function isPublished($entityClass, $id, $locale): bool
    {
        /*$archive = $this->repository->findById((int)$id, $locale);
        if (!$this->supports($entityClass) || !$archive instanceof Archive) {
            return false;
        }
        return $archive->isPublished();*/
        return true;
    }

    public function supports($entityClass): bool
    {
        return Archive::class === $entityClass;
    }
}
