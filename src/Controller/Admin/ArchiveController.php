<?php

declare(strict_types=1);

namespace Manuxi\SuluArchiveBundle\Controller\Admin;

use Manuxi\SuluArchiveBundle\Common\DoctrineListRepresentationFactory;
use Manuxi\SuluArchiveBundle\Entity\Archive;
use Manuxi\SuluArchiveBundle\Entity\Models\ArchiveExcerptModel;
use Manuxi\SuluArchiveBundle\Entity\Models\ArchiveModel;
use Manuxi\SuluArchiveBundle\Entity\Models\ArchiveSeoModel;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\View\ViewHandlerInterface;
use Manuxi\SuluArchiveBundle\Search\Event\ArchivePublishedEvent;
use Manuxi\SuluArchiveBundle\Search\Event\ArchiveRemovedEvent;
use Manuxi\SuluArchiveBundle\Search\Event\ArchiveSavedEvent;
use Manuxi\SuluArchiveBundle\Search\Event\ArchiveUnpublishedEvent;
use Sulu\Bundle\TrashBundle\Application\TrashManager\TrashManagerInterface;
use Sulu\Component\Rest\AbstractRestController;
use Sulu\Component\Rest\Exception\MissingParameterException;
use Sulu\Component\Rest\Exception\RestException;
use Sulu\Component\Rest\RequestParametersTrait;
use Sulu\Component\Security\Authorization\PermissionTypes;
use Sulu\Component\Security\Authorization\SecurityCheckerInterface;
use Sulu\Component\Security\Authorization\SecurityCondition;
use Sulu\Component\Security\SecuredControllerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @RouteResource("archive")
 */
class ArchiveController extends AbstractRestController implements ClassResourceInterface, SecuredControllerInterface
{
    use RequestParametersTrait;

    public function __construct(
        private readonly ArchiveModel                      $archiveModel,
        private readonly ArchiveSeoModel                   $archiveSeoModel,
        private readonly ArchiveExcerptModel               $archiveExcerptModel,
        private readonly DoctrineListRepresentationFactory $doctrineListRepresentationFactory,
        private readonly SecurityCheckerInterface          $securityChecker,
        private readonly TrashManagerInterface             $trashManager,
        private readonly EventDispatcherInterface $dispatcher,
        ViewHandlerInterface                               $viewHandler,
        ?TokenStorageInterface                             $tokenStorage = null
    ) {
        parent::__construct($viewHandler, $tokenStorage);
    }

    #[Route('/archives', name: 'get_archives', methods: 'GET')]
    public function cgetAction(Request $request): Response
    {
        $locale             = $request->query->get('locale');
        $listRepresentation = $this->doctrineListRepresentationFactory->createDoctrineListRepresentation(
            Archive::RESOURCE_KEY,
            [],
            ['locale' => $locale]
        );

        return $this->handleView($this->view($listRepresentation));
    }

    #[Route('/archive/{id}', name: 'get_archive', methods: 'GET')]
    public function getAction(int $id, Request $request): Response
    {
        $entity = $this->archiveModel->getArchive($id, $request);
        return $this->handleView($this->view($entity));
    }

    #[Route('/archive', name: 'post_archive', methods: 'POST')]
    public function postAction(Request $request): Response
    {
        $entity = $this->archiveModel->createArchive($request);
        $this->dispatcher->dispatch(new ArchiveSavedEvent($entity));
        return $this->handleView($this->view($entity, 201));
    }

    /**
     * @Rest\Post("/archives/{id}")
     *
     * @param int $id
     * @param Request $request
     * @return Response
     * @throws MissingParameterException
     */
    #[Route('/archives/{id}', name: 'post_archive_trigger', methods: 'POST')]
    public function postTriggerAction(int $id, Request $request): Response
    {
        $action = $this->getRequestParameter($request, 'action', true);

        try {
            switch ($action) {
                case 'publish':
                    $entity = $this->archiveModel->publishArchive($id, $request);
                    $this->dispatcher->dispatch(new ArchivePublishedEvent($entity));
                    break;
                case 'draft':
                case 'unpublish':
                    $entity = $this->archiveModel->unpublishArchive($id, $request);
                    $this->dispatcher->dispatch(new ArchiveUnpublishedEvent($entity));
                    break;
                case 'copy':
                    $entity = $this->archiveModel->copy($id, $request);
                    $this->dispatcher->dispatch(new ArchiveSavedEvent($entity));
                    break;
                case 'copy-locale':
                    $locale = $this->getRequestParameter($request, 'locale', true);
                    $srcLocale = $this->getRequestParameter($request, 'src', false, $locale);
                    $destLocales = $this->getRequestParameter($request, 'dest', true);
                    $destLocales = explode(',', $destLocales);

                    foreach ($destLocales as $destLocale) {
                        $this->securityChecker->checkPermission(
                            new SecurityCondition($this->getSecurityContext(), $destLocale),
                            PermissionTypes::EDIT
                        );
                    }

                    $entity = $this->archiveModel->copyLanguage($id, $request, $srcLocale, $destLocales);
                    $this->dispatcher->dispatch(new ArchiveSavedEvent($entity));
                    break;
                default:
                    throw new BadRequestHttpException(sprintf('Unknown action "%s".', $action));
            }
        } catch (RestException $exc) {
            $view = $this->view($exc->toArray(), 400);
            return $this->handleView($view);
        }
        return $this->handleView($this->view($entity));
    }

    #[Route('/archive/{id}', name: 'put_archive', methods: 'PUT')]
    public function putAction(int $id, Request $request): Response
    {
        try {
            $action = $this->getRequestParameter($request, 'action', true);
            try {
                $entity = match ($action) {
                    'publish' => $this->archiveModel->publishArchive($id, $request),
                    'draft', 'unpublish' => $this->archiveModel->unpublishArchive($id, $request),
                    default => throw new BadRequestHttpException(sprintf('Unknown action "%s".', $action)),
                };
            } catch (RestException $exc) {
                $view = $this->view($exc->toArray(), 400);
                return $this->handleView($view);
            }
        } catch(MissingParameterException $e) {
            $entity = $this->archiveModel->updateArchive($id, $request);

            $this->dispatcher->dispatch(new ArchiveSavedEvent($entity));

            $this->archiveSeoModel->updateArchiveSeo($entity->getArchiveSeo(), $request);
            $this->archiveExcerptModel->updateArchiveExcerpt($entity->getArchiveExcerpt(), $request);
        }

        return $this->handleView($this->view($entity));
    }

    #[Route('/archive/{id}', name: 'delete_archive', methods: 'DELETE')]
    public function deleteAction(int $id): Response
    {
        $entity = $this->archiveModel->getArchive($id);

        $this->trashManager->store(Archive::RESOURCE_KEY, $entity);

        $this->archiveModel->deleteArchive($entity);

        $this->dispatcher->dispatch(new ArchiveRemovedEvent($entity));

        return $this->handleView($this->view(null, 204));
    }

    public function getSecurityContext(): string
    {
        return Archive::SECURITY_CONTEXT;
    }

}
