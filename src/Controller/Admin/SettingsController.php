<?php

declare(strict_types=1);

namespace Manuxi\SuluArchiveBundle\Controller\Admin;

use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\View\ViewHandlerInterface;
use HandcraftedInTheAlps\RestRoutingBundle\Controller\Annotations\RouteResource;
use HandcraftedInTheAlps\RestRoutingBundle\Routing\ClassResourceInterface;
use Manuxi\SuluArchiveBundle\Domain\Event\Settings\ModifiedEvent;
use Manuxi\SuluArchiveBundle\Entity\ArchiveSettings;
use Sulu\Bundle\ActivityBundle\Application\Collector\DomainEventCollectorInterface;
use Sulu\Component\Rest\AbstractRestController;
use Sulu\Component\Security\SecuredControllerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @RouteResource("archive-settings")
 */
class SettingsController extends AbstractRestController implements ClassResourceInterface, SecuredControllerInterface
{
    private EntityManagerInterface $entityManager;
    private DomainEventCollectorInterface $domainEventCollector;

    public function __construct(
        EntityManagerInterface $entityManager,
        ViewHandlerInterface $viewHandler,
        DomainEventCollectorInterface $domainEventCollector,
        ?TokenStorageInterface $tokenStorage = null
    ) {
        $this->entityManager = $entityManager;
        $this->domainEventCollector = $domainEventCollector;

        parent::__construct($viewHandler, $tokenStorage);
    }

    public function getAction(): Response
    {
        $entity = $this->entityManager->getRepository(ArchiveSettings::class)->findOneBy([]);

        return $this->handleView($this->view($this->getDataForEntity($entity ?: new ArchiveSettings())));
    }

    public function putAction(Request $request): Response
    {
        $entity = $this->entityManager->getRepository(ArchiveSettings::class)->findOneBy([]);
        if (!$entity) {
            $entity = new ArchiveSettings();
            $this->entityManager->persist($entity);
        }

        $this->domainEventCollector->collect(
            new ModifiedEvent($entity, $request->request->all())
        );

        $data = $request->toArray();
        $this->mapDataToEntity($data, $entity);
        $this->entityManager->flush();

        return $this->handleView($this->view($this->getDataForEntity($entity)));
    }

    protected function getDataForEntity(ArchiveSettings $entity): array
    {
        return [
            'toggleHeader' => $entity->getToggleHeader(),
            'toggleHero' => $entity->getToggleHero(),
            'toggleBreadcrumbs' => $entity->getToggleBreadcrumbs(),
            'pageArchive' => $entity->getPageArchive(),
            'pageArchiveDefault' => $entity->getPageArchiveDefault(),
            'pageArchiveStreets' => $entity->getPageArchiveStreets(),
            'pageArchiveTraffic' => $entity->getPageArchiveTraffic(),
            'pageArchiveTrains' => $entity->getPageArchiveTrains(),
            'pageArchiveSigns' => $entity->getPageArchiveSigns(),
            'pageArchiveAttractions' => $entity->getPageArchiveAttractions(),
            'pageArchiveMemorials' => $entity->getPageArchiveMemorials(),
            'pageArchiveBuildings' => $entity->getPageArchiveBuildings(),
            'pageArchiveMining' => $entity->getPageArchiveMining(),
            'pageArchiveSurroundingArea' => $entity->getPageArchiveSurroundingArea(),
            'pageArchiveMapsPlans' => $entity->getPageArchiveMapsPlans(),
            'pageArchiveAerialShots' => $entity->getPageArchiveAerialShots(),
            'pageArchiveDevelopmentPlans' => $entity->getPageArchiveDevelopmentPlans(),
            'pageArchiveExpertOpinionsReports' => $entity->getPageArchiveExpertOpinionsReports(),
            'pageArchivePlaceNameStudies' => $entity->getPageArchivePlaceNameStudies(),
            'pageArchiveLocalChronicles' => $entity->getPageArchiveLocalChronicles(),
            'pageArchiveNewspaperArticles' => $entity->getPageArchiveNewspaperArticles(),
            'pageArchiveAdvertisements' => $entity->getPageArchiveAdvertisements(),
            'pageArchivePostersFlyers' => $entity->getPageArchivePostersFlyers(),
            'pageArchiveObjectsArtifacts' => $entity->getPageArchiveObjectsArtifacts(),
            'pageArchiveCollectionsExhibitions' => $entity->getPageArchiveCollectionsExhibitions(),
            'pageArchiveGenealogicalResearch' => $entity->getPageArchiveGenealogicalResearch(),
            'pageArchiveBiographies' => $entity->getPageArchiveBiographies(),
            'pageArchiveCorrespondences' => $entity->getPageArchiveCorrespondences(),
            'pageArchiveHistoricalDocuments' => $entity->getPageArchiveHistoricalDocuments(),
            'pageArchiveHistoricalRecordings' => $entity->getPageArchiveHistoricalRecordings(),
            'pageArchiveVisualMaterial' => $entity->getPageArchiveVisualMaterial(),
            'pageArchiveAudioVideoRecordings' => $entity->getPageArchiveAudioVideoRecordings(),
            'pageArchiveMembershipDirectories' => $entity->getPageArchiveMembershipDirectories(),
            'pageArchiveAssociationMagazinesDocuments' => $entity->getPageArchiveAssociationMagazinesDocuments(),

        ];
    }

    protected function mapDataToEntity(array $data, ArchiveSettings $entity): void
    {
        $entity->setToggleHeader($data['toggleHeader']);
        $entity->setToggleHero($data['toggleHero']);
        $entity->setToggleBreadcrumbs($data['toggleBreadcrumbs']);
        $entity->setPageArchive($data['pageArchive']);
        $entity->setPageArchiveDefault($data['pageArchiveDefault']);
        $entity->setPageArchiveStreets($data['pageArchiveStreets']);
        $entity->setPageArchiveTraffic($data['pageArchiveTraffic']);
        $entity->setPageArchiveTrains($data['pageArchiveTrains']);
        $entity->setPageArchiveSigns($data['pageArchiveSigns']);
        $entity->setPageArchiveAttractions($data['pageArchiveAttractions']);
        $entity->setPageArchiveMemorials($data['pageArchiveMemorials']);
        $entity->setPageArchiveBuildings($data['pageArchiveBuildings']);
        $entity->setPageArchiveMining($data['pageArchiveMining']);
        $entity->setPageArchiveSurroundingArea($data['pageArchiveSurroundingArea']);
        $entity->setPageArchiveMapsPlans($data['pageArchiveMapsPlans']);
        $entity->setPageArchiveAerialShots($data['pageArchiveAerialShots']);
        $entity->setPageArchiveDevelopmentPlans($data['pageArchiveDevelopmentPlans']);
        $entity->setPageArchiveExpertOpinionsReports($data['pageArchiveExpertOpinionsReports']);
        $entity->setPageArchivePlaceNameStudies($data['pageArchivePlaceNameStudies']);
        $entity->setPageArchiveLocalChronicles($data['pageArchiveLocalChronicles']);
        $entity->setPageArchiveNewspaperArticles($data['pageArchiveNewspaperArticles']);
        $entity->setPageArchiveAdvertisements($data['pageArchiveAdvertisements']);
        $entity->setPageArchivePostersFlyers($data['pageArchivePostersFlyers']);
        $entity->setPageArchiveObjectsArtifacts($data['pageArchiveObjectsArtifacts']);
        $entity->setPageArchiveCollectionsExhibitions($data['pageArchiveCollectionsExhibitions']);
        $entity->setPageArchiveGenealogicalResearch($data['pageArchiveGenealogicalResearch']);
        $entity->setPageArchiveBiographies($data['pageArchiveBiographies']);
        $entity->setPageArchiveCorrespondences($data['pageArchiveCorrespondences']);
        $entity->setPageArchiveHistoricalDocuments($data['pageArchiveHistoricalDocuments']);
        $entity->setPageArchiveHistoricalRecordings($data['pageArchiveHistoricalRecordings']);
        $entity->setPageArchiveVisualMaterial($data['pageArchiveVisualMaterial']);
        $entity->setPageArchiveAudioVideoRecordings($data['pageArchiveAudioVideoRecordings']);
        $entity->setPageArchiveMembershipDirectories($data['pageArchiveMembershipDirectories']);
        $entity->setPageArchiveAssociationMagazinesDocuments($data['pageArchiveAssociationMagazinesDocuments']);
    }

    public function getSecurityContext(): string
    {
        return ArchiveSettings::SECURITY_CONTEXT;
    }

    public function getLocale(Request $request): ?string
    {
        return $request->query->get('locale');
    }
}