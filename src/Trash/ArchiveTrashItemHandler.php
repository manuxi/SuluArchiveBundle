<?php

declare(strict_types=1);

namespace Manuxi\SuluArchiveBundle\Trash;

use Doctrine\ORM\EntityManagerInterface;
use Manuxi\SuluArchiveBundle\Admin\ArchiveAdmin;
use Manuxi\SuluArchiveBundle\Domain\Event\ArchiveRestoredEvent;
use Manuxi\SuluArchiveBundle\Entity\Archive;
use Manuxi\SuluSharedToolsBundle\Search\Event\PersistedEvent as SearchPersistedEvent;
use Manuxi\SuluSharedToolsBundle\Search\Event\RemovedEvent as SearchRemovedEvent;
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
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class ArchiveTrashItemHandler implements StoreTrashItemHandlerInterface, RestoreTrashItemHandlerInterface, RestoreConfigurationProviderInterface
{
    public function __construct(
        private readonly TrashItemRepositoryInterface $trashItemRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly DoctrineRestoreHelperInterface $doctrineRestoreHelper,
        private readonly DomainEventCollectorInterface $domainEventCollector,
        private readonly EventDispatcherInterface $dispatcher,
    ) {
    }

    public static function getResourceKey(): string
    {
        return Archive::RESOURCE_KEY;
    }

    public function store(object $entity, array $options = []): TrashItemInterface
    {
        /* @var Archive $entity */

        $image = $entity->getImage();
        $document = $entity->getDocument();

        $data = [
            'locale' => $entity->getLocale(),
            'type' => $entity->getType(),
            'title' => $entity->getTitle(),
            'subtitle' => $entity->getSubtitle(),
            'summary' => $entity->getSummary(),
            'text' => $entity->getText(),
            'footer' => $entity->getFooter(),
            'slug' => $entity->getRoutePath(),
            'ext' => $entity->getExt(),
            'link' => $entity->getLink(),
            'imageId' => $image?->getId(),
            'documentId' => $document?->getId(),
            'published' => $entity->isPublished(),
            'publishedAt' => $entity->getPublishedAt(),
            'showAuthor' => $entity->getShowAuthor(),
            'showDate' => $entity->getShowDate(),
            'authored' => $entity->getAuthored(),
            'author' => $entity->getAuthor(),
        ];

        $this->dispatcher->dispatch(new SearchRemovedEvent($entity));

        return $this->trashItemRepository->create(
            Archive::RESOURCE_KEY,
            (string) $entity->getId(),
            $entity->getTitle(),
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
        $archiveId = (int) $trashItem->getResourceId();
        $archive = new Archive();
        $archive->setLocale($data['locale']);

        $archive->setType($data['type']);
        $archive->setTitle($data['title']);
        $archive->setSubtitle($data['subtitle']);
        $archive->setSummary($data['summary']);
        $archive->setText($data['text']);
        $archive->setFooter($data['footer']);
        $archive->setPublished($data['published']);
        $archive->setPublishedAt($data['publishedAt'] ? new \DateTime($data['publishedAt']['date']) : null);
        $archive->setShowAuthor($data['showAuthor']);
        $archive->setShowDate($data['showDate']);
        $archive->setRoutePath($data['slug']);
        $archive->setExt($data['ext']);

        $archive->setAuthored($data['authored'] ? new \DateTime($data['authored']['date']) : new \DateTime());

        if ($data['author']) {
            $contact = $this->entityManager->find(ContactInterface::class, $data['author']);
            $archive->setAuthor($contact);
        }

        if ($data['link']) {
            $archive->setLink($data['link']);
        }

        if ($data['imageId']) {
            $image = $this->entityManager->find(MediaInterface::class, $data['imageId']);
            $archive->setImage($image);
        }

        if ($data['documentId']) {
            $document = $this->entityManager->find(MediaInterface::class, $data['documentId']);
            $archive->setDocument($document);
        }

        $this->domainEventCollector->collect(
            new ArchiveRestoredEvent($archive, $data)
        );

        $this->doctrineRestoreHelper->persistAndFlushWithId($archive, $archiveId);
        $this->createRoute($this->entityManager, $archiveId, $data['locale'], $archive->getRoutePath(), Archive::class);
        $this->entityManager->flush();

        $this->dispatcher->dispatch(new SearchPersistedEvent($archive));

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
        $route->setCreated(new \DateTime());
        $route->setChanged(new \DateTime());
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
