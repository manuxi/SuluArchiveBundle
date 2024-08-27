<?php

namespace Manuxi\SuluArchiveBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Sulu\Component\Persistence\Model\AuditableInterface;
use Sulu\Component\Persistence\Model\AuditableTrait;

#[ORM\Entity()]
#[ORM\Table(name: 'app_archive_settings')]
class ArchiveSettings implements AuditableInterface
{
    use AuditableTrait;

    public const RESOURCE_KEY = 'archive_settings';
    public const FORM_KEY = 'archive_config';
    public const SECURITY_CONTEXT = 'sulu.archive.settings';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: Types::BOOLEAN, nullable: true)]
    private ?bool $toggleHeader = null;

    #[ORM\Column(type: Types::BOOLEAN, nullable: true)]
    private ?bool $toggleHero = null;

    #[ORM\Column(type: Types::BOOLEAN, nullable: true)]
    private ?bool $toggleBreadcrumbs = null;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $pageArchive = null;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $pageArchiveDefault = null;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $pageArchiveStreets = null;


    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $pageArchiveAttractions = null;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $pageArchiveBuildings = null;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $pageArchiveMining = null;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $pageArchiveSurroundingArea = null;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $pageArchiveMapsPlans = null;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $pageArchiveAerialShots = null;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $pageArchiveDevelopmentPlans = null;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $pageArchiveExpertOpinionsReports = null;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $pageArchivePlaceNameStudies = null;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $pageArchiveLocalChronicles = null;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $pageArchiveNewspaperArticles = null;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $pageArchiveAdvertisements = null;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $pageArchivePostersFlyers = null;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $pageArchiveObjectsArtifacts = null;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $pageArchiveCollectionsExhibitions = null;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $pageArchiveGenealogicalResearch = null;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $pageArchiveBiographies = null;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $pageArchiveCorrespondences = null;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $pageArchiveHistoricalDocuments = null;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $pageArchiveHistoricalRecordings = null;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $pageArchiveVisualMaterial = null;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $pageArchiveAudioVideoRecordings = null;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $pageArchiveMembershipDirectories = null;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $pageArchiveAssociationMagazinesDocuments = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getToggleHeader(): ?bool
    {
        return $this->toggleHeader;
    }

    public function setToggleHeader(?bool $toggleHeader): void
    {
        $this->toggleHeader = $toggleHeader;
    }

    public function getToggleHero(): ?bool
    {
        return $this->toggleHero;
    }

    public function setToggleHero(?bool $toggleHero): void
    {
        $this->toggleHero = $toggleHero;
    }

    public function getToggleBreadcrumbs(): ?bool
    {
        return $this->toggleBreadcrumbs;
    }

    public function setToggleBreadcrumbs(?bool $toggleBreadcrumbs): void
    {
        $this->toggleBreadcrumbs = $toggleBreadcrumbs;
    }

    public function getPageArchive(): ?string
    {
        return $this->pageArchive;
    }

    public function setPageArchive(?string $pageArchive): void
    {
        $this->pageArchive = $pageArchive;
    }

    public function getPageArchiveDefault(): ?string
    {
        return $this->pageArchiveDefault;
    }

    public function setPageArchiveDefault(?string $pageArchiveDefault): void
    {
        $this->pageArchiveDefault = $pageArchiveDefault;
    }

    public function getPageArchiveStreets(): ?string
    {
        return $this->pageArchiveStreets;
    }

    public function setPageArchiveStreets(?string $pageArchiveStreets): void
    {
        $this->pageArchiveStreets = $pageArchiveStreets;
    }

    public function getPageArchiveAttractions(): ?string
    {
        return $this->pageArchiveAttractions;
    }

    public function setPageArchiveAttractions(?string $pageArchiveAttractions): void
    {
        $this->pageArchiveAttractions = $pageArchiveAttractions;
    }

    public function getPageArchiveBuildings(): ?string
    {
        return $this->pageArchiveBuildings;
    }

    public function setPageArchiveBuildings(?string $pageArchiveBuildings): void
    {
        $this->pageArchiveBuildings = $pageArchiveBuildings;
    }

    public function getPageArchiveMining(): ?string
    {
        return $this->pageArchiveMining;
    }

    public function setPageArchiveMining(?string $pageArchiveMining): void
    {
        $this->pageArchiveMining = $pageArchiveMining;
    }

    public function getPageArchiveSurroundingArea(): ?string
    {
        return $this->pageArchiveSurroundingArea;
    }

    public function setPageArchiveSurroundingArea(?string $pageArchiveSurroundingArea): void
    {
        $this->pageArchiveSurroundingArea = $pageArchiveSurroundingArea;
    }

    public function getPageArchiveMapsPlans(): ?string
    {
        return $this->pageArchiveMapsPlans;
    }

    public function setPageArchiveMapsPlans(?string $pageArchiveMapsPlans): void
    {
        $this->pageArchiveMapsPlans = $pageArchiveMapsPlans;
    }

    public function getPageArchiveAerialShots(): ?string
    {
        return $this->pageArchiveAerialShots;
    }

    public function setPageArchiveAerialShots(?string $pageArchiveAerialShots): void
    {
        $this->pageArchiveAerialShots = $pageArchiveAerialShots;
    }

    public function getPageArchiveDevelopmentPlans(): ?string
    {
        return $this->pageArchiveDevelopmentPlans;
    }

    public function setPageArchiveDevelopmentPlans(?string $pageArchiveDevelopmentPlans): void
    {
        $this->pageArchiveDevelopmentPlans = $pageArchiveDevelopmentPlans;
    }

    public function getPageArchiveExpertOpinionsReports(): ?string
    {
        return $this->pageArchiveExpertOpinionsReports;
    }

    public function setPageArchiveExpertOpinionsReports(?string $pageArchiveExpertOpinionsReports): void
    {
        $this->pageArchiveExpertOpinionsReports = $pageArchiveExpertOpinionsReports;
    }

    public function getPageArchivePlaceNameStudies(): ?string
    {
        return $this->pageArchivePlaceNameStudies;
    }

    public function setPageArchivePlaceNameStudies(?string $pageArchivePlaceNameStudies): void
    {
        $this->pageArchivePlaceNameStudies = $pageArchivePlaceNameStudies;
    }

    public function getPageArchiveLocalChronicles(): ?string
    {
        return $this->pageArchiveLocalChronicles;
    }

    public function setPageArchiveLocalChronicles(?string $pageArchiveLocalChronicles): void
    {
        $this->pageArchiveLocalChronicles = $pageArchiveLocalChronicles;
    }

    public function getPageArchiveNewspaperArticles(): ?string
    {
        return $this->pageArchiveNewspaperArticles;
    }

    public function setPageArchiveNewspaperArticles(?string $pageArchiveNewspaperArticles): void
    {
        $this->pageArchiveNewspaperArticles = $pageArchiveNewspaperArticles;
    }

    public function getPageArchiveAdvertisements(): ?string
    {
        return $this->pageArchiveAdvertisements;
    }

    public function setPageArchiveAdvertisements(?string $pageArchiveAdvertisements): void
    {
        $this->pageArchiveAdvertisements = $pageArchiveAdvertisements;
    }

    public function getPageArchivePostersFlyers(): ?string
    {
        return $this->pageArchivePostersFlyers;
    }

    public function setPageArchivePostersFlyers(?string $pageArchivePostersFlyers): void
    {
        $this->pageArchivePostersFlyers = $pageArchivePostersFlyers;
    }

    public function getPageArchiveObjectsArtifacts(): ?string
    {
        return $this->pageArchiveObjectsArtifacts;
    }

    public function setPageArchiveObjectsArtifacts(?string $pageArchiveObjectsArtifacts): void
    {
        $this->pageArchiveObjectsArtifacts = $pageArchiveObjectsArtifacts;
    }

    public function getPageArchiveCollectionsExhibitions(): ?string
    {
        return $this->pageArchiveCollectionsExhibitions;
    }

    public function setPageArchiveCollectionsExhibitions(?string $pageArchiveCollectionsExhibitions): void
    {
        $this->pageArchiveCollectionsExhibitions = $pageArchiveCollectionsExhibitions;
    }

    public function getPageArchiveGenealogicalResearch(): ?string
    {
        return $this->pageArchiveGenealogicalResearch;
    }

    public function setPageArchiveGenealogicalResearch(?string $pageArchiveGenealogicalResearch): void
    {
        $this->pageArchiveGenealogicalResearch = $pageArchiveGenealogicalResearch;
    }

    public function getPageArchiveBiographies(): ?string
    {
        return $this->pageArchiveBiographies;
    }

    public function setPageArchiveBiographies(?string $pageArchiveBiographies): void
    {
        $this->pageArchiveBiographies = $pageArchiveBiographies;
    }

    public function getPageArchiveCorrespondences(): ?string
    {
        return $this->pageArchiveCorrespondences;
    }

    public function setPageArchiveCorrespondences(?string $pageArchiveCorrespondences): void
    {
        $this->pageArchiveCorrespondences = $pageArchiveCorrespondences;
    }

    public function getPageArchiveHistoricalDocuments(): ?string
    {
        return $this->pageArchiveHistoricalDocuments;
    }

    public function setPageArchiveHistoricalDocuments(?string $pageArchiveHistoricalDocuments): void
    {
        $this->pageArchiveHistoricalDocuments = $pageArchiveHistoricalDocuments;
    }

    public function getPageArchiveHistoricalRecordings(): ?string
    {
        return $this->pageArchiveHistoricalRecordings;
    }

    public function setPageArchiveHistoricalRecordings(?string $pageArchiveHistoricalRecordings): void
    {
        $this->pageArchiveHistoricalRecordings = $pageArchiveHistoricalRecordings;
    }

    public function getPageArchiveVisualMaterial(): ?string
    {
        return $this->pageArchiveVisualMaterial;
    }

    public function setPageArchiveVisualMaterial(?string $pageArchiveVisualMaterial): void
    {
        $this->pageArchiveVisualMaterial = $pageArchiveVisualMaterial;
    }

    public function getPageArchiveAudioVideoRecordings(): ?string
    {
        return $this->pageArchiveAudioVideoRecordings;
    }

    public function setPageArchiveAudioVideoRecordings(?string $pageArchiveAudioVideoRecordings): void
    {
        $this->pageArchiveAudioVideoRecordings = $pageArchiveAudioVideoRecordings;
    }

    public function getPageArchiveMembershipDirectories(): ?string
    {
        return $this->pageArchiveMembershipDirectories;
    }

    public function setPageArchiveMembershipDirectories(?string $pageArchiveMembershipDirectories): void
    {
        $this->pageArchiveMembershipDirectories = $pageArchiveMembershipDirectories;
    }

    public function getPageArchiveAssociationMagazinesDocuments(): ?string
    {
        return $this->pageArchiveAssociationMagazinesDocuments;
    }

    public function setPageArchiveAssociationMagazinesDocuments(?string $pageArchiveAssociationMagazinesDocuments): void
    {
        $this->pageArchiveAssociationMagazinesDocuments = $pageArchiveAssociationMagazinesDocuments;
    }

}