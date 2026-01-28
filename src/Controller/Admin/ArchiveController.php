<?php

declare(strict_types=1);

namespace Manuxi\SuluArchiveBundle\Controller\Admin;

use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\View\ViewHandlerInterface;
use Manuxi\SuluArchiveBundle\Domain\Event\Archive\CreatedEvent;
use Manuxi\SuluArchiveBundle\Domain\Event\Archive\ModifiedEvent;
use Manuxi\SuluArchiveBundle\Domain\Event\Archive\PublishedEvent;
use Manuxi\SuluArchiveBundle\Domain\Event\Archive\RemovedEvent;
use Manuxi\SuluArchiveBundle\Domain\Event\Archive\RestoredEvent;
use Manuxi\SuluArchiveBundle\Domain\Event\Archive\UnpublishedEvent;
use Manuxi\SuluArchiveBundle\Entity\Archive;
use Manuxi\SuluArchiveBundle\Entity\ArchiveDimensionContent;
use Manuxi\SuluArchiveBundle\ListBuilder\DoctrineListRepresentationFactory;
use Manuxi\SuluArchiveBundle\Repository\ArchiveRepository;
use Sulu\Bundle\ActivityBundle\Application\Collector\DomainEventCollectorInterface;
use Sulu\Bundle\TrashBundle\Application\TrashManager\TrashManagerInterface;
use Sulu\Component\Rest\AbstractRestController;
use Sulu\Component\Rest\Exception\RestException;
use Sulu\Component\Rest\ListBuilder\Doctrine\DoctrineListBuilder;
use Sulu\Component\Rest\ListBuilder\Doctrine\DoctrineListBuilderFactoryInterface;
use Sulu\Component\Rest\ListBuilder\Doctrine\FieldDescriptor\DoctrineFieldDescriptorInterface;
use Sulu\Component\Rest\ListBuilder\Metadata\FieldDescriptorFactoryInterface;
use Sulu\Component\Rest\ListBuilder\PaginatedRepresentation;
use Sulu\Component\Rest\RestHelperInterface;
use Sulu\Content\Application\ContentManager\ContentManagerInterface;
use Sulu\Content\Application\ContentWorkflow\ContentWorkflowInterface;
use Sulu\Content\Domain\Model\DimensionContentInterface;
use Sulu\Content\Domain\Model\WorkflowInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[Route('/admin/api')]
class ArchiveController extends AbstractRestController
{
    public function __construct(
        ViewHandlerInterface $viewHandler,
        TokenStorageInterface $tokenStorage,
        private FieldDescriptorFactoryInterface $fieldDescriptorFactory,
        private DoctrineListBuilderFactoryInterface $listBuilderFactory,
        private RestHelperInterface $restHelper,
        private ContentManagerInterface $contentManager,
        private EntityManagerInterface $entityManager,
        private DoctrineListRepresentationFactory $doctrineListRepresentationFactory,
        private DomainEventCollectorInterface $domainEventCollector,
        private TrashManagerInterface $trashManager,
        private ContentWorkflowInterface $contentWorkflow,
    ) {
        parent::__construct($viewHandler, $tokenStorage);
    }

    #[Route(
        path: '/archives.{_format}',
        name: 'sulu_archive.get_archives',
        options: ['expose' => true],
        defaults: ['_format' => 'json'],
        methods: ['GET']
    )]
    public function cgetAction(Request $request): Response
    {
        $listKey = null;
        if ($request->query->has('selectedIds')) {
            $listKey = Archive::LIST_KEY_PUBLISHED;
        }

        $filters = [];
        $parameters = $request->query->all();

        $listRepresentation = $this->doctrineListRepresentationFactory->createDoctrineListRepresentation(
            Archive::RESOURCE_KEY,
            $filters,
            $parameters,
            $listKey
        );

        return $this->handleView($this->view($listRepresentation));
    }

    #[Route(
        path: '/archives/{id}.{_format}',
        name: 'sulu_archive.get_archive',
        options: ['expose' => true],
        defaults: ['_format' => 'json'],
        methods: ['GET']
    )]
    public function getAction(Request $request, string $id): Response
    {
        /** @var ArchiveRepository $archiveRepository */
        $archiveRepository = $this->entityManager->getRepository(Archive::class);
        /** @var Archive|null $archive */
        $archive = $archiveRepository->findByUuid($id);

        if (!$archive) {
            throw new NotFoundHttpException();
        }

        $dimensionAttributes = $this->getDimensionAttributes($request);
        $dimensionContent = $this->contentManager->resolve($archive, $dimensionAttributes);

        return $this->handleView($this->view($this->normalize($archive, $dimensionContent)));
    }

    #[Route(
        path: '/archives.{_format}',
        name: 'sulu_archive.post_archive',
        options: ['expose' => true],
        defaults: ['_format' => 'json'],
        methods: ['POST']
    )]
    public function postAction(Request $request): Response
    {
        $archive = new Archive();

        $data = $this->getData($request);
        $dimensionAttributes = $this->getDimensionAttributes($request);

        $this->entityManager->persist($archive);

        /** @var ArchiveDimensionContent $dimensionContent */
        $dimensionContent = $this->contentManager->persist($archive, $data, $dimensionAttributes);

        $this->domainEventCollector->collect(new CreatedEvent($archive, $data));
        $this->entityManager->flush();

        if ('publish' === $request->query->get('action')) {
            $this->contentWorkflow->apply(
                $archive,
                ['locale' => $dimensionAttributes['locale']],
                WorkflowInterface::WORKFLOW_TRANSITION_PUBLISH
            );
            $dimensionContent = $this->contentManager->resolve($archive, $dimensionAttributes);
            $this->entityManager->flush();
            $this->domainEventCollector->collect(new PublishedEvent($archive, $data));
        }

        return $this->handleView($this->view($this->normalize($archive, $dimensionContent), 201));
    }

    #[Route(
        path: '/archives/{id}.{_format}',
        name: 'sulu_archive.put_archive',
        options: ['expose' => true],
        defaults: ['_format' => 'json'],
        methods: ['PUT']
    )]
    public function putAction(Request $request, string $id): Response
    {
        /** @var ArchiveRepository $archiveRepository */
        $archiveRepository = $this->entityManager->getRepository(Archive::class);
        /** @var Archive|null $archive */
        $archive = $archiveRepository->findByUuid($id);

        if (!$archive) {
            throw new NotFoundHttpException();
        }

        $data = $this->getData($request);
        $dimensionAttributes = $this->getDimensionAttributes($request);

        /** @var ArchiveDimensionContent $dimensionContent */
        $dimensionContent = $this->contentManager->persist($archive, $data, $dimensionAttributes);

        if (WorkflowInterface::WORKFLOW_PLACE_PUBLISHED === $dimensionContent->getWorkflowPlace()) {
            $this->contentWorkflow->apply(
                $archive,
                ['locale' => $dimensionAttributes['locale']],
                WorkflowInterface::WORKFLOW_TRANSITION_CREATE_DRAFT
            );
            $dimensionContent = $this->contentManager->resolve($archive, $dimensionAttributes);
        }

        $this->domainEventCollector->collect(new ModifiedEvent($archive, $data));
        $this->entityManager->flush();

        if ('publish' === $request->query->get('action')) {
            $this->contentWorkflow->apply(
                $archive,
                ['locale' => $dimensionAttributes['locale']],
                WorkflowInterface::WORKFLOW_TRANSITION_PUBLISH
            );
            $dimensionContent = $this->contentManager->resolve($archive, $dimensionAttributes);
            $this->domainEventCollector->collect(new PublishedEvent($archive, $data));
            $this->entityManager->flush();
        }

        return $this->handleView($this->view($this->normalize($archive, $dimensionContent)));
    }

    #[Route(
        path: '/archives/{id}.{_format}',
        name: 'sulu_archive.delete_archive',
        options: ['expose' => true],
        defaults: ['_format' => 'json'],
        methods: ['DELETE']
    )]
    public function deleteAction(Request $request, string $id): Response
    {
        /** @var ArchiveRepository $archiveRepository */
        $archiveRepository = $this->entityManager->getRepository(Archive::class);
        /** @var Archive|null $archive */
        $archive = $archiveRepository->findByUuid($id);

        if (!$archive) {
            throw new NotFoundHttpException();
        }

        $archiveUuid = $archive->getUuid();
        $archiveTitle = '';

        $locale = $request->query->get('locale');
        if ($locale) {
            foreach ($archive->getDimensionContents() as $dc) {
                if ($dc->getLocale() === $locale) {
                    $archiveTitle = $dc->getTitle() ?? '';
                    break;
                }
            }
        }

        $this->trashManager->store(Archive::RESOURCE_KEY, $archive);
        $this->entityManager->remove($archive);
        $this->domainEventCollector->collect(new RemovedEvent($archiveUuid, $archiveTitle));
        $this->entityManager->flush();

        return $this->handleView($this->view(null, 204));
    }

    #[Route(
        path: '/archives/{id}.{_format}',
        name: 'sulu_archive.post_trigger',
        options: ['expose' => true],
        defaults: ['_format' => 'json'],
        methods: ['POST']
    )]
    public function postTriggerAction(Request $request, string $id): Response
    {
        $action = $request->query->get('action');

        /** @var ArchiveRepository $archiveRepository */
        $archiveRepository = $this->entityManager->getRepository(Archive::class);
        /** @var Archive|null $archive */
        $archive = $archiveRepository->findByUuid($id);

        if (!$archive) {
            throw new NotFoundHttpException();
        }

        $dimensionAttributes = $this->getDimensionAttributes($request);
        $locale = $dimensionAttributes['locale'];

        switch ($action) {
            case 'copy_locale':
                $dimensionContent = $this->contentManager->copy(
                    $archive,
                    [
                        'stage' => DimensionContentInterface::STAGE_DRAFT,
                        'locale' => $request->query->get('src'),
                    ],
                    $archive,
                    [
                        'stage' => DimensionContentInterface::STAGE_DRAFT,
                        'locale' => $request->query->get('dest'),
                    ]
                );

                $this->entityManager->flush();

                return $this->handleView($this->view($this->normalize($archive, $dimensionContent)));

            case 'publish':
                $this->contentWorkflow->apply(
                    $archive,
                    ['locale' => $locale],
                    WorkflowInterface::WORKFLOW_TRANSITION_PUBLISH
                );
                $dimensionContent = $this->contentManager->resolve($archive, $dimensionAttributes);

                $payload = $request->query->all();
                $payload['title'] = $dimensionContent->getTitle();
                $this->domainEventCollector->collect(new PublishedEvent($archive, $payload));
                $this->entityManager->flush();

                return $this->handleView($this->view($this->normalize($archive, $dimensionContent)));

            case 'unpublish':
                $this->contentWorkflow->apply(
                    $archive,
                    ['locale' => $locale],
                    WorkflowInterface::WORKFLOW_TRANSITION_UNPUBLISH
                );
                $dimensionContent = $this->contentManager->resolve($archive, $dimensionAttributes);

                $payload = $request->query->all();
                $payload['title'] = $dimensionContent->getTitle();
                $this->domainEventCollector->collect(new UnpublishedEvent($archive, $payload));
                $this->entityManager->flush();

                return $this->handleView($this->view($this->normalize($archive, $dimensionContent)));

            case 'remove_draft':
                $this->contentWorkflow->apply(
                    $archive,
                    ['locale' => $dimensionAttributes['locale']],
                    WorkflowInterface::WORKFLOW_TRANSITION_REMOVE_DRAFT
                );
                $dimensionContent = $this->contentManager->resolve($archive, $dimensionAttributes);

                $this->entityManager->flush();

                return $this->handleView($this->view($this->normalize($archive, $dimensionContent)));

            case 'restore':
                $version = (int) $request->query->get('version');
                $dimensionContent = $this->contentManager->copy(
                    $archive,
                    [
                        'stage' => $dimensionAttributes['stage'] ?? DimensionContentInterface::STAGE_DRAFT,
                        'locale' => $dimensionAttributes['locale'],
                        'version' => $version,
                    ],
                    $archive,
                    [
                        'stage' => DimensionContentInterface::STAGE_DRAFT,
                        'locale' => $dimensionAttributes['locale'],
                    ]
                );

                $payload = $request->query->all();
                $payload['title'] = $dimensionContent->getTitle();
                $this->domainEventCollector->collect(new RestoredEvent($archive, $payload));
                $this->entityManager->flush();

                return $this->handleView($this->view($this->normalize($archive, $dimensionContent)));

            default:
                throw new RestException('Unrecognized action: ' . $action);
        }
    }

    #[Route(
        path: '/archives/{id}/versions.{_format}',
        name: 'sulu_archive.get_archive_versions',
        options: ['expose' => true],
        defaults: ['_format' => 'json'],
        methods: ['GET']
    )]
    public function getVersionsAction(Request $request, string $id): Response
    {
        $locale = $request->query->get('locale');

        /** @var DoctrineFieldDescriptorInterface[] $fieldDescriptors */
        $fieldDescriptors = $this->fieldDescriptorFactory->getFieldDescriptors('archives_versions');

        /** @var DoctrineListBuilder $listBuilder */
        $listBuilder = $this->listBuilderFactory->create(Archive::class);
        $listBuilder->setParameter('locale', $locale);
        $listBuilder->setParameter('archiveUuid', $id);
        $listBuilder->setIdField($fieldDescriptors['id']);
        $listBuilder->sort($fieldDescriptors['version'], 'DESC');
        $this->restHelper->initializeListBuilder($listBuilder, $fieldDescriptors);

        $result = $listBuilder->execute();
        $listRepresentation = new PaginatedRepresentation(
            $result,
            'archives_versions',
            (int) $listBuilder->getCurrentPage(),
            (int) $listBuilder->getLimit(),
            $listBuilder->count(),
        );

        return $this->handleView($this->view($listRepresentation));
    }

    protected function getDimensionAttributes(Request $request): array
    {
        return [
            'locale' => $request->query->get('locale', $request->getLocale()),
            'stage' => DimensionContentInterface::STAGE_DRAFT,
        ];
    }

    protected function getData(Request $request): array
    {
        if ('application/json' === $request->headers->get('Content-Type')) {
            return $request->toArray();
        }

        return $request->request->all();
    }

    protected function normalize(Archive $archive, DimensionContentInterface $dimensionContent): array
    {
        $normalized = $this->contentManager->normalize($dimensionContent);

        return array_merge($normalized, [
            'id' => $archive->getUuid(),
            'uuid' => $archive->getUuid(),
        ]);
    }
}
