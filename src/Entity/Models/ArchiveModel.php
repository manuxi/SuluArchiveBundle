<?php

declare(strict_types=1);

namespace Manuxi\SuluArchiveBundle\Entity\Models;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Manuxi\SuluArchiveBundle\Domain\Event\ArchiveCopiedLanguageEvent;
use Manuxi\SuluArchiveBundle\Domain\Event\ArchiveCreatedEvent;
use Manuxi\SuluArchiveBundle\Domain\Event\ArchiveModifiedEvent;
use Manuxi\SuluArchiveBundle\Domain\Event\ArchivePublishedEvent;
use Manuxi\SuluArchiveBundle\Domain\Event\ArchiveRemovedEvent;
use Manuxi\SuluArchiveBundle\Domain\Event\ArchiveUnpublishedEvent;
use Manuxi\SuluArchiveBundle\Entity\Archive;
use Manuxi\SuluArchiveBundle\Entity\Interfaces\ArchiveModelInterface;
use Manuxi\SuluArchiveBundle\Entity\Traits\ArrayPropertyTrait;
use Manuxi\SuluArchiveBundle\Repository\ArchiveRepository;
use Sulu\Bundle\ActivityBundle\Application\Collector\DomainEventCollectorInterface;
use Sulu\Bundle\ContactBundle\Entity\ContactRepository;
use Sulu\Bundle\MediaBundle\Entity\MediaRepositoryInterface;
use Sulu\Bundle\RouteBundle\Entity\RouteRepositoryInterface;
use Sulu\Bundle\RouteBundle\Manager\RouteManagerInterface;
use Sulu\Component\Rest\Exception\EntityNotFoundException;
use Symfony\Component\HttpFoundation\Request;

class ArchiveModel implements ArchiveModelInterface
{
    use ArrayPropertyTrait;

    public function __construct(
        private ArchiveRepository $archiveRepository,
        private MediaRepositoryInterface $mediaRepository,
        private ContactRepository $contactRepository,
        private RouteManagerInterface $routeManager,
        private RouteRepositoryInterface $routeRepository,
        private EntityManagerInterface $entityManager,
        private DomainEventCollectorInterface $domainEventCollector
    ) {}

    /**
     * @param int $id
     * @param Request|null $request
     * @return Archive
     * @throws EntityNotFoundException
     */
    public function getArchive(int $id, Request $request = null): Archive
    {
        if(null === $request) {
            return $this->findArchiveById($id);
        }
        return $this->findArchiveByIdAndLocale($id, $request);
    }

    public function deleteArchive(Archive $entity): void
    {
        $this->domainEventCollector->collect(
            new ArchiveRemovedEvent($entity->getId(), $entity->getTitle() ?? '')
        );
        $this->removeRoutesForEntity($entity);
        $this->archiveRepository->remove($entity->getId());
    }

    /**
     * @param Request $request
     * @return Archive
     * @throws EntityNotFoundException
     */
    public function createArchive(Request $request): Archive
    {
        $entity = $this->archiveRepository->create((string) $this->getLocaleFromRequest($request));
        $entity = $this->mapDataToArchive($entity, $request->request->all());

        $this->domainEventCollector->collect(
            new ArchiveCreatedEvent($entity, $request->request->all())
        );

        //need the id for updateRoutesForEntity(), so we have to persist and flush here
        $entity = $this->archiveRepository->save($entity);

        $this->updateRoutesForEntity($entity);

        //explicit flush to save routes persisted by updateRoutesForEntity()
        $this->entityManager->flush();

        return $entity;
    }

    /**
     * @param int $id
     * @param Request $request
     * @return Archive
     * @throws EntityNotFoundException
     */
    public function updateArchive(int $id, Request $request): Archive
    {
        $entity = $this->findArchiveByIdAndLocale($id, $request);
        $entity = $this->mapDataToArchive($entity, $request->request->all());
        $entity = $this->mapSettingsToArchive($entity, $request->request->all());
        $this->updateRoutesForEntity($entity);

        $this->domainEventCollector->collect(
            new ArchiveModifiedEvent($entity, $request->request->all())
        );

        return $this->archiveRepository->save($entity);
    }

    /**
     * @param int $id
     * @param Request $request
     * @return Archive
     * @throws EntityNotFoundException
     */
    public function publishArchive(int $id, Request $request): Archive
    {
        $entity = $this->findArchiveByIdAndLocale($id, $request);

        $this->domainEventCollector->collect(
            new ArchivePublishedEvent($entity, $request->request->all())
        );

        return $this->archiveRepository->publish($entity);
    }

    /**
     * @param int $id
     * @param Request $request
     * @return Archive
     * @throws EntityNotFoundException
     */
    public function unpublishArchive(int $id, Request $request): Archive
    {
        $entity = $this->findArchiveByIdAndLocale($id, $request);
        $entity->setPublished(false);

        $this->domainEventCollector->collect(
            new ArchiveUnpublishedEvent($entity, $request->request->all())
        );

        return $this->archiveRepository->unpublish($entity);
    }

    public function copy(int $id, Request $request): Archive
    {
        $locale = $this->getLocaleFromRequest($request);

        $entity = $this->findArchiveById($id);
        $entity->setLocale($locale);

        $copy = $this->archiveRepository->create($locale);

        $copy = $entity->copy($copy);
        return $this->archiveRepository->save($copy);
    }

    public function copyLanguage(int $id, Request $request, string $srcLocale, array $destLocales): Archive
    {
        $entity = $this->findArchiveById($id);
        $entity->setLocale($srcLocale);

        foreach($destLocales as $destLocale) {
            $entity = $entity->copyToLocale($destLocale);
        }

        //@todo: test with more than one different locale
        $entity->setLocale($this->getLocaleFromRequest($request));

        $this->domainEventCollector->collect(
            new ArchiveCopiedLanguageEvent($entity, $request->request->all())
        );

        return $this->archiveRepository->save($entity);
    }

    /**
     * @param int $id
     * @param Request $request
     * @return Archive
     * @throws EntityNotFoundException
     */
    private function findArchiveByIdAndLocale(int $id, Request $request): Archive
    {
        $entity = $this->archiveRepository->findById($id, (string) $this->getLocaleFromRequest($request));
        if (!$entity) {
            throw new EntityNotFoundException($this->archiveRepository->getClassName(), $id);
        }
        return $entity;
    }

    /**
     * @param int $id
     * @return Archive
     * @throws EntityNotFoundException
     */
    private function findArchiveById(int $id): Archive
    {
        $entity = $this->archiveRepository->find($id);
        if (!$entity) {
            throw new EntityNotFoundException($this->archiveRepository->getClassName(), $id);
        }
        return $entity;
    }

    private function getLocaleFromRequest(Request $request): ?string
    {
        return $request->query->get('locale');
    }

    /**
     * @param Archive $entity
     * @param array $data
     * @return Archive
     * @throws EntityNotFoundException
     * @throws Exception
     */
    private function mapDataToArchive(Archive $entity, array $data): Archive
    {
        $title = $this->getProperty($data, 'title');
        if ($title) {
            $entity->setTitle($title);
        }

        $text = $this->getProperty($data, 'text');
        if ($text) {
            $entity->setText($text);
        }

        $type = $this->getProperty($data, 'type');
        if ($type) {
            $entity->setType($type);
        }

        $routePath = $this->getProperty($data, 'routePath');
        if ($routePath) {
            $entity->setRoutePath($routePath);
        }

        $showAuthor = $this->getProperty($data, 'showAuthor');
        $entity->setShowAuthor($showAuthor ? true : false);

        $showDate = $this->getProperty($data, 'showDate');
        $entity->setShowDate($showDate ? true : false);

        $subtitle = $this->getProperty($data, 'subtitle');
        $entity->setSubtitle($subtitle ?: null);

        $summary = $this->getProperty($data, 'summary');
        $entity->setSummary($summary ?: null);

        $footer = $this->getProperty($data, 'footer');
        $entity->setFooter($footer ?: null);

        $link = $this->getProperty($data, 'link');
        $entity->setLink($link ?: null);

        $images = $this->getProperty($data, 'images');
        $entity->setImages($images ?: null);

        $imageId = $this->getPropertyMulti($data, ['image', 'id']);
        if ($imageId) {
            $image = $this->mediaRepository->findMediaById((int) $imageId);
            if (!$image) {
                throw new EntityNotFoundException($this->mediaRepository->getClassName(), $imageId);
            }
            $entity->setImage($image);
        } else {
            $entity->setImage(null);
        }

        $documentId = $this->getPropertyMulti($data, ['document', 'id']);
        if ($documentId) {
            $document = $this->mediaRepository->findMediaById((int) $documentId);
            if (!$document) {
                throw new EntityNotFoundException($this->mediaRepository->getClassName(), $documentId);
            }
            $entity->setDocument($document);
        } else {
            $entity->setDocument(null);
        }

        return $entity;
    }

    /**
     * @param Archive $entity
     * @param array $data
     * @return Archive
     * @throws EntityNotFoundException
     * @throws Exception
     */
    private function mapSettingsToArchive(Archive $entity, array $data): Archive
    {
        //settings (author, authored) changeable
        $authorId = $this->getProperty($data, 'author');
        if ($authorId) {
            $author = $this->contactRepository->findById($authorId);
            if (!$author) {
                throw new EntityNotFoundException($this->contactRepository->getClassName(), $authorId);
            }
            $entity->setAuthor($author);
        } else {
            $entity->setAuthor(null);
        }

        $authored = $this->getProperty($data, 'authored');
        if ($authored) {
            $entity->setAuthored(new DateTime($authored));
        } else {
            $entity->setAuthored(null);
        }
        return $entity;
    }

    private function updateRoutesForEntity(Archive $entity): void
    {
        $this->routeManager->createOrUpdateByAttributes(
            Archive::class,
            (string) $entity->getId(),
            $entity->getLocale(),
            $entity->getRoutePath()
        );
    }

    private function removeRoutesForEntity(Archive $entity): void
    {
        $routes = $this->routeRepository->findAllByEntity(
            Archive::class,
            (string) $entity->getId(),
            $entity->getLocale()
        );

        foreach ($routes as $route) {
            $this->routeRepository->remove($route);
        }
    }
}
