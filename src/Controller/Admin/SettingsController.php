<?php

declare(strict_types=1);

namespace Manuxi\SuluArchiveBundle\Controller\Admin;

use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\View\ViewHandlerInterface;
use Manuxi\SuluArchiveBundle\Domain\Event\Settings\ModifiedEvent;
use Manuxi\SuluArchiveBundle\Entity\ArchiveSettings;
use Sulu\Bundle\ActivityBundle\Application\Collector\DomainEventCollectorInterface;
use Sulu\Component\Rest\AbstractRestController;
use Sulu\Component\Security\SecuredControllerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[Route('/admin/api')]
class SettingsController extends AbstractRestController implements SecuredControllerInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        ViewHandlerInterface $viewHandler,
        private DomainEventCollectorInterface $domainEventCollector,
        ?TokenStorageInterface $tokenStorage = null,
    ) {
        parent::__construct($viewHandler, $tokenStorage);
    }

    #[Route(
        '/archive-settings/{id}.{_format}',
        name: 'sulu_archive.get_archive-settings',
        requirements: [
            '_format' => 'json|csv',
        ],
        options: ['expose' => true],
        defaults: [
            '_format' => 'json',
        ],
        methods: ['GET']
    )]
    public function getAction(): Response
    {
        $entity = $this->entityManager->getRepository(ArchiveSettings::class)->findOneBy([]);

        return $this->handleView($this->view($this->getDataForEntity($entity ?: new ArchiveSettings())));
    }

    #[Route(
        '/archive-settings/{id}.{_format}',
        name: 'sulu_archive.put_archive-settings',
        requirements: [
            '_format' => 'json',
        ],
        options: ['expose' => true],
        defaults: ['_format' => 'json'],
        methods: ['PUT']
    )]
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
            'toggleTags' => $entity->getToggleTags(),
            'toggleCategories' => $entity->getToggleCategories(),
            'colorTags' => $entity->getColorTags(),
            'colorCategories' => $entity->getColorCategories(),
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
            'pageArchiveClubJournals' => $entity->getPageArchiveClubJournals(),
        ];
    }

    protected function mapDataToEntity(array $data, ArchiveSettings $entity): void
    {
        $entity->setToggleHeader($data['toggleHeader'] ?? null);
        $entity->setToggleHero($data['toggleHero'] ?? null);
        $entity->setToggleBreadcrumbs($data['toggleBreadcrumbs'] ?? null);
        $entity->setToggleTags($data['toggleTags'] ?? null);
        $entity->setToggleCategories($data['toggleCategories'] ?? null);
        $entity->setColorTags($data['colorTags'] ?? null);
        $entity->setColorCategories($data['colorCategories'] ?? null);
        $entity->setPageArchive($data['pageArchive'] ?? null);
        $entity->setPageArchiveDefault($data['pageArchiveDefault'] ?? null);
        $entity->setPageArchiveStreets($data['pageArchiveStreets'] ?? null);
        $entity->setPageArchiveTraffic($data['pageArchiveTraffic'] ?? null);
        $entity->setPageArchiveTrains($data['pageArchiveTrains'] ?? null);
        $entity->setPageArchiveSigns($data['pageArchiveSigns'] ?? null);
        $entity->setPageArchiveAttractions($data['pageArchiveAttractions'] ?? null);
        $entity->setPageArchiveMemorials($data['pageArchiveMemorials'] ?? null);
        $entity->setPageArchiveBuildings($data['pageArchiveBuildings'] ?? null);
        $entity->setPageArchiveMining($data['pageArchiveMining'] ?? null);
        $entity->setPageArchiveSurroundingArea($data['pageArchiveSurroundingArea'] ?? null);
        $entity->setPageArchiveMapsPlans($data['pageArchiveMapsPlans'] ?? null);
        $entity->setPageArchiveAerialShots($data['pageArchiveAerialShots'] ?? null);
        $entity->setPageArchiveDevelopmentPlans($data['pageArchiveDevelopmentPlans'] ?? null);
        $entity->setPageArchiveExpertOpinionsReports($data['pageArchiveExpertOpinionsReports'] ?? null);
        $entity->setPageArchivePlaceNameStudies($data['pageArchivePlaceNameStudies'] ?? null);
        $entity->setPageArchiveLocalChronicles($data['pageArchiveLocalChronicles'] ?? null);
        $entity->setPageArchiveNewspaperArticles($data['pageArchiveNewspaperArticles'] ?? null);
        $entity->setPageArchiveAdvertisements($data['pageArchiveAdvertisements'] ?? null);
        $entity->setPageArchivePostersFlyers($data['pageArchivePostersFlyers'] ?? null);
        $entity->setPageArchiveObjectsArtifacts($data['pageArchiveObjectsArtifacts'] ?? null);
        $entity->setPageArchiveCollectionsExhibitions($data['pageArchiveCollectionsExhibitions'] ?? null);
        $entity->setPageArchiveGenealogicalResearch($data['pageArchiveGenealogicalResearch'] ?? null);
        $entity->setPageArchiveBiographies($data['pageArchiveBiographies'] ?? null);
        $entity->setPageArchiveCorrespondences($data['pageArchiveCorrespondences'] ?? null);
        $entity->setPageArchiveHistoricalDocuments($data['pageArchiveHistoricalDocuments'] ?? null);
        $entity->setPageArchiveHistoricalRecordings($data['pageArchiveHistoricalRecordings'] ?? null);
        $entity->setPageArchiveVisualMaterial($data['pageArchiveVisualMaterial'] ?? null);
        $entity->setPageArchiveAudioVideoRecordings($data['pageArchiveAudioVideoRecordings'] ?? null);
        $entity->setPageArchiveMembershipDirectories($data['pageArchiveMembershipDirectories'] ?? null);
        $entity->setPageArchiveClubJournals($data['pageArchiveClubJournals'] ?? null);
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
