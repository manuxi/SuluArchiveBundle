<?php

declare(strict_types=1);

namespace Manuxi\SuluArchiveBundle\Trash;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Manuxi\SuluArchiveBundle\Admin\ArchiveAdmin;
use Manuxi\SuluArchiveBundle\Domain\Event\ArchiveRestoredEvent;
use Manuxi\SuluArchiveBundle\Entity\Archive;
use Sulu\Bundle\ActivityBundle\Application\Collector\DomainEventCollectorInterface;
use Sulu\Bundle\ContactBundle\Entity\ContactInterface;
use Sulu\Bundle\MediaBundle\Entity\MediaInterface;
use Sulu\Bundle\RouteBundle\Entity\Route;
use Sulu\Bundle\TrashBundle\Application\DoctrineRestoreHelper\DoctrineRestoreHelperInterface;
use Sulu\Bundle\TrashBundle\Application\RestoreConfigurationProvider\RestoreConfiguration;
use Sulu\Bundle\TrashBundle\Application\RestoreConfigurationProvider\RestoreConfigurationProviderInterface;
use Sulu\Bundle\TrashBundle\Application\TrashItemHandler\RestoreTrashItemHandlerInterface;
use Sulu\Bundle\TrashBundle\Application\TrashItemHandler\StoreTrashItemHandlerInterface;
use Sulu\Bundle\TrashBundle\Domain\Model\TrashItemInterface;
use Sulu\Bundle\TrashBundle\Domain\Repository\TrashItemRepositoryInterface;

class ArchiveTrashItemHandler implements StoreTrashItemHandlerInterface, RestoreTrashItemHandlerInterface, RestoreConfigurationProviderInterface
{
    private TrashItemRepositoryInterface $trashItemRepository;
    private EntityManagerInterface $entityManager;
    private DoctrineRestoreHelperInterface $doctrineRestoreHelper;
    private DomainEventCollectorInterface $domainEventCollector;

    public function __construct(
        TrashItemRepositoryInterface   $trashItemRepository,
        EntityManagerInterface         $entityManager,
        DoctrineRestoreHelperInterface $doctrineRestoreHelper,
        DomainEventCollectorInterface  $domainEventCollector
    )
    {
        $this->trashItemRepository = $trashItemRepository;
        $this->entityManager = $entityManager;
        $this->doctrineRestoreHelper = $doctrineRestoreHelper;
        $this->domainEventCollector = $domainEventCollector;
    }

    public static function getResourceKey(): string
    {
        return Archive::RESOURCE_KEY;
    }

    public function store(object $resource, array $options = []): TrashItemInterface
    {
        $image = $resource->getImage();

        $data = [
            "locale" => $resource->getLocale(),
            "type" => $resource->getType(),
            "title" => $resource->getTitle(),
            "subtitle" => $resource->getSubtitle(),
            "summary" => $resource->getSummary(),
            "text" => $resource->getText(),
            "footer" => $resource->getFooter(),
            "slug" => $resource->getRoutePath(),
            "ext" => $resource->getExt(),
            "link" => $resource->getLink(),
            "imageId" => $image ? $image->getId() : null,
            "published" => $resource->isPublished(),
            "publishedAt" => $resource->getPublishedAt(),
            "showAuthor" => $resource->getShowAuthor(),
            "showDate" => $resource->getShowDate(),
            "authored" => $resource->getAuthored(),
            "author" => $resource->getAuthor(),
        ];
        return $this->trashItemRepository->create(
            Archive::RESOURCE_KEY,
            (string)$resource->getId(),
            $resource->getTitle(),
            $data,
            null,
            $options,
            Archive::SECURITY_CONTEXT,
            null,
            null
        );
    }

    public function restore(TrashItemInterface $trashItem, array $restoreFormData = []): object
    {
        $data = $trashItem->getRestoreData();
        $archiveId = (int)$trashItem->getResourceId();
        $archive = new Archive();
        $archive->setLocale($data['locale']);

        $archive->setType($data['type']);
        $archive->setTitle($data['title']);
        $archive->setSubtitle($data['subtitle']);
        $archive->setSummary($data['summary']);
        $archive->setText($data['text']);
        $archive->setFooter($data['footer']);
        $archive->setPublished($data['published']);
        $archive->setShowAuthor($data['showAuthor']);
        $archive->setShowDate($data['showDate']);
        $archive->setRoutePath($data['slug']);
        $archive->setExt($data['ext']);

        $archive->setAuthored($data['authored'] ? new DateTime($data['authored']['date']) : new DateTime());

        if ($data['author']) {
            $contact = $this->entityManager->find(ContactInterface::class, $data['author']);
            $archive->setAuthor($contact);
        }

        if($data['link']) {
            $archive->setLink($data['link']);
        }

        if($data['imageId']) {
            $image = $this->entityManager->find(MediaInterface::class, $data['imageId']);
            $archive->setImage($image);
        }

        if(isset($data['publishedAt'])) {
            $archive->setPublishedAt(new DateTime($data['publishedAt']['date']));
        }

        $this->domainEventCollector->collect(
            new ArchiveRestoredEvent($archive, $data)
        );

        $this->doctrineRestoreHelper->persistAndFlushWithId($archive, $archiveId);
        $this->createRoute($this->entityManager, $archiveId, $data['locale'], $archive->getRoutePath(), Archive::class);
        $this->entityManager->flush();
        return $archive;
    }

    private function createRoute(EntityManagerInterface $manager, int $id, string $locale, string $slug, string $class): void
    {
        $route = new Route();
        $route->setPath($slug);
        $route->setLocale($locale);
        $route->setEntityClass($class);
        $route->setEntityId($id);
        $route->setHistory(0);
        $route->setCreated(new DateTime());
        $route->setChanged(new DateTime());
        $manager->persist($route);
    }

    public function getConfiguration(): RestoreConfiguration
    {
        return new RestoreConfiguration(
            null,
            ArchiveAdmin::EDIT_FORM_VIEW,
            ['id' => 'id']
        );
    }
}
