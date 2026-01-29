<?php

declare(strict_types=1);

namespace Manuxi\SuluArchiveBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Sulu\Component\Persistence\Model\AuditableInterface;
use Sulu\Component\Persistence\Model\AuditableTrait;

class ArchiveSettings implements AuditableInterface
{
    use AuditableTrait;

    public const RESOURCE_KEY = 'archive_settings';
    public const FORM_KEY = 'archive_config';
    public const SECURITY_CONTEXT = 'sulu.archive.settings';

    private ?int $id = null;

    private ?bool $toggleHeader = null;

    private ?bool $toggleHero = null;

    private ?bool $toggleBreadcrumbs = null;

    private ?bool $toggleTags = null;

    private ?bool $toggleCategories = null;

    private ?string $colorTags = null;

    private ?string $colorCategories = null;

    private ?string $pageArchive = null;

    private ?string $pageArchiveDefault = null;

    private ?string $pageArchiveStreets = null;

    private ?string $pageArchiveTraffic = null;

    private ?string $pageArchiveTrains = null;

    private ?string $pageArchiveSigns = null;

    private ?string $pageArchiveAttractions = null;

    private ?string $pageArchiveMemorials = null;

    private ?string $pageArchiveBuildings = null;

    private ?string $pageArchiveMining = null;

    private ?string $pageArchiveSurroundingArea = null;

    private ?string $pageArchiveMapsPlans = null;

    private ?string $pageArchiveAerialShots = null;

    private ?string $pageArchiveDevelopmentPlans = null;

    private ?string $pageArchiveExpertOpinionsReports = null;

    private ?string $pageArchivePlaceNameStudies = null;

    private ?string $pageArchiveLocalChronicles = null;

    private ?string $pageArchiveNewspaperArticles = null;

    private ?string $pageArchiveAdvertisements = null;

    private ?string $pageArchivePostersFlyers = null;

    private ?string $pageArchiveObjectsArtifacts = null;

    private ?string $pageArchiveCollectionsExhibitions = null;

    private ?string $pageArchiveGenealogicalResearch = null;

    private ?string $pageArchiveBiographies = null;

    private ?string $pageArchiveCorrespondences = null;

    private ?string $pageArchiveHistoricalDocuments = null;

    private ?string $pageArchiveHistoricalRecordings = null;

    private ?string $pageArchiveVisualMaterial = null;

    private ?string $pageArchiveAudioVideoRecordings = null;

    private ?string $pageArchiveMembershipDirectories = null;

    private ?string $pageArchiveClubJournals = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getToggleHeader(): ?bool
    {
        return $this->toggleHeader;
    }

    public function setToggleHeader(?bool $toggleHeader): self
    {
        $this->toggleHeader = $toggleHeader;
        return $this;
    }

    public function getToggleHero(): ?bool
    {
        return $this->toggleHero;
    }

    public function setToggleHero(?bool $toggleHero): self
    {
        $this->toggleHero = $toggleHero;
        return $this;
    }

    public function getToggleBreadcrumbs(): ?bool
    {
        return $this->toggleBreadcrumbs;
    }

    public function setToggleBreadcrumbs(?bool $toggleBreadcrumbs): self
    {
        $this->toggleBreadcrumbs = $toggleBreadcrumbs;
        return $this;
    }

    public function getToggleTags(): ?bool
    {
        return $this->toggleTags;
    }

    public function setToggleTags(?bool $toggleTags): self
    {
        $this->toggleTags = $toggleTags;
        return $this;
    }

    public function getToggleCategories(): ?bool
    {
        return $this->toggleCategories;
    }

    public function setToggleCategories(?bool $toggleCategories): self
    {
        $this->toggleCategories = $toggleCategories;
        return $this;
    }

    public function getColorTags(): ?string
    {
        return $this->colorTags;
    }

    public function setColorTags(?string $colorTags): self
    {
        $this->colorTags = $colorTags;
        return $this;
    }

    public function getColorCategories(): ?string
    {
        return $this->colorCategories;
    }

    public function setColorCategories(?string $colorCategories): self
    {
        $this->colorCategories = $colorCategories;
        return $this;
    }

    public function getPageArchive(): ?string
    {
        return $this->pageArchive;
    }

    public function setPageArchive(?string $pageArchive): self
    {
        $this->pageArchive = $pageArchive;
        return $this;
    }

    public function getPageArchiveDefault(): ?string
    {
        return $this->pageArchiveDefault;
    }

    public function setPageArchiveDefault(?string $pageArchiveDefault): self
    {
        $this->pageArchiveDefault = $pageArchiveDefault;
        return $this;
    }

    public function getPageArchiveStreets(): ?string
    {
        return $this->pageArchiveStreets;
    }

    public function setPageArchiveStreets(?string $pageArchiveStreets): self
    {
        $this->pageArchiveStreets = $pageArchiveStreets;
        return $this;
    }

    public function getPageArchiveTraffic(): ?string
    {
        return $this->pageArchiveTraffic;
    }

    public function setPageArchiveTraffic(?string $pageArchiveTraffic): self
    {
        $this->pageArchiveTraffic = $pageArchiveTraffic;
        return $this;
    }

    public function getPageArchiveTrains(): ?string
    {
        return $this->pageArchiveTrains;
    }

    public function setPageArchiveTrains(?string $pageArchiveTrains): self
    {
        $this->pageArchiveTrains = $pageArchiveTrains;
        return $this;
    }

    public function getPageArchiveSigns(): ?string
    {
        return $this->pageArchiveSigns;
    }

    public function setPageArchiveSigns(?string $pageArchiveSigns): self
    {
        $this->pageArchiveSigns = $pageArchiveSigns;
        return $this;
    }

    public function getPageArchiveAttractions(): ?string
    {
        return $this->pageArchiveAttractions;
    }

    public function setPageArchiveAttractions(?string $pageArchiveAttractions): self
    {
        $this->pageArchiveAttractions = $pageArchiveAttractions;
        return $this;
    }

    public function getPageArchiveMemorials(): ?string
    {
        return $this->pageArchiveMemorials;
    }

    public function setPageArchiveMemorials(?string $pageArchiveMemorials): self
    {
        $this->pageArchiveMemorials = $pageArchiveMemorials;
        return $this;
    }

    public function getPageArchiveBuildings(): ?string
    {
        return $this->pageArchiveBuildings;
    }

    public function setPageArchiveBuildings(?string $pageArchiveBuildings): self
    {
        $this->pageArchiveBuildings = $pageArchiveBuildings;
        return $this;
    }

    public function getPageArchiveMining(): ?string
    {
        return $this->pageArchiveMining;
    }

    public function setPageArchiveMining(?string $pageArchiveMining): self
    {
        $this->pageArchiveMining = $pageArchiveMining;
        return $this;
    }

    public function getPageArchiveSurroundingArea(): ?string
    {
        return $this->pageArchiveSurroundingArea;
    }

    public function setPageArchiveSurroundingArea(?string $pageArchiveSurroundingArea): self
    {
        $this->pageArchiveSurroundingArea = $pageArchiveSurroundingArea;
        return $this;
    }

    public function getPageArchiveMapsPlans(): ?string
    {
        return $this->pageArchiveMapsPlans;
    }

    public function setPageArchiveMapsPlans(?string $pageArchiveMapsPlans): self
    {
        $this->pageArchiveMapsPlans = $pageArchiveMapsPlans;
        return $this;
    }

    public function getPageArchiveAerialShots(): ?string
    {
        return $this->pageArchiveAerialShots;
    }

    public function setPageArchiveAerialShots(?string $pageArchiveAerialShots): self
    {
        $this->pageArchiveAerialShots = $pageArchiveAerialShots;
        return $this;
    }

    public function getPageArchiveDevelopmentPlans(): ?string
    {
        return $this->pageArchiveDevelopmentPlans;
    }

    public function setPageArchiveDevelopmentPlans(?string $pageArchiveDevelopmentPlans): self
    {
        $this->pageArchiveDevelopmentPlans = $pageArchiveDevelopmentPlans;
        return $this;
    }

    public function getPageArchiveExpertOpinionsReports(): ?string
    {
        return $this->pageArchiveExpertOpinionsReports;
    }

    public function setPageArchiveExpertOpinionsReports(?string $pageArchiveExpertOpinionsReports): self
    {
        $this->pageArchiveExpertOpinionsReports = $pageArchiveExpertOpinionsReports;
        return $this;
    }

    public function getPageArchivePlaceNameStudies(): ?string
    {
        return $this->pageArchivePlaceNameStudies;
    }

    public function setPageArchivePlaceNameStudies(?string $pageArchivePlaceNameStudies): self
    {
        $this->pageArchivePlaceNameStudies = $pageArchivePlaceNameStudies;
        return $this;
    }

    public function getPageArchiveLocalChronicles(): ?string
    {
        return $this->pageArchiveLocalChronicles;
    }

    public function setPageArchiveLocalChronicles(?string $pageArchiveLocalChronicles): self
    {
        $this->pageArchiveLocalChronicles = $pageArchiveLocalChronicles;
        return $this;
    }

    public function getPageArchiveNewspaperArticles(): ?string
    {
        return $this->pageArchiveNewspaperArticles;
    }

    public function setPageArchiveNewspaperArticles(?string $pageArchiveNewspaperArticles): self
    {
        $this->pageArchiveNewspaperArticles = $pageArchiveNewspaperArticles;
        return $this;
    }

    public function getPageArchiveAdvertisements(): ?string
    {
        return $this->pageArchiveAdvertisements;
    }

    public function setPageArchiveAdvertisements(?string $pageArchiveAdvertisements): self
    {
        $this->pageArchiveAdvertisements = $pageArchiveAdvertisements;
        return $this;
    }

    public function getPageArchivePostersFlyers(): ?string
    {
        return $this->pageArchivePostersFlyers;
    }

    public function setPageArchivePostersFlyers(?string $pageArchivePostersFlyers): self
    {
        $this->pageArchivePostersFlyers = $pageArchivePostersFlyers;
        return $this;
    }

    public function getPageArchiveObjectsArtifacts(): ?string
    {
        return $this->pageArchiveObjectsArtifacts;
    }

    public function setPageArchiveObjectsArtifacts(?string $pageArchiveObjectsArtifacts): self
    {
        $this->pageArchiveObjectsArtifacts = $pageArchiveObjectsArtifacts;
        return $this;
    }

    public function getPageArchiveCollectionsExhibitions(): ?string
    {
        return $this->pageArchiveCollectionsExhibitions;
    }

    public function setPageArchiveCollectionsExhibitions(?string $pageArchiveCollectionsExhibitions): self
    {
        $this->pageArchiveCollectionsExhibitions = $pageArchiveCollectionsExhibitions;
        return $this;
    }

    public function getPageArchiveGenealogicalResearch(): ?string
    {
        return $this->pageArchiveGenealogicalResearch;
    }

    public function setPageArchiveGenealogicalResearch(?string $pageArchiveGenealogicalResearch): self
    {
        $this->pageArchiveGenealogicalResearch = $pageArchiveGenealogicalResearch;
        return $this;
    }

    public function getPageArchiveBiographies(): ?string
    {
        return $this->pageArchiveBiographies;
    }

    public function setPageArchiveBiographies(?string $pageArchiveBiographies): self
    {
        $this->pageArchiveBiographies = $pageArchiveBiographies;
        return $this;
    }

    public function getPageArchiveCorrespondences(): ?string
    {
        return $this->pageArchiveCorrespondences;
    }

    public function setPageArchiveCorrespondences(?string $pageArchiveCorrespondences): self
    {
        $this->pageArchiveCorrespondences = $pageArchiveCorrespondences;
        return $this;
    }

    public function getPageArchiveHistoricalDocuments(): ?string
    {
        return $this->pageArchiveHistoricalDocuments;
    }

    public function setPageArchiveHistoricalDocuments(?string $pageArchiveHistoricalDocuments): self
    {
        $this->pageArchiveHistoricalDocuments = $pageArchiveHistoricalDocuments;
        return $this;
    }

    public function getPageArchiveHistoricalRecordings(): ?string
    {
        return $this->pageArchiveHistoricalRecordings;
    }

    public function setPageArchiveHistoricalRecordings(?string $pageArchiveHistoricalRecordings): self
    {
        $this->pageArchiveHistoricalRecordings = $pageArchiveHistoricalRecordings;
        return $this;
    }

    public function getPageArchiveVisualMaterial(): ?string
    {
        return $this->pageArchiveVisualMaterial;
    }

    public function setPageArchiveVisualMaterial(?string $pageArchiveVisualMaterial): self
    {
        $this->pageArchiveVisualMaterial = $pageArchiveVisualMaterial;
        return $this;
    }

    public function getPageArchiveAudioVideoRecordings(): ?string
    {
        return $this->pageArchiveAudioVideoRecordings;
    }

    public function setPageArchiveAudioVideoRecordings(?string $pageArchiveAudioVideoRecordings): self
    {
        $this->pageArchiveAudioVideoRecordings = $pageArchiveAudioVideoRecordings;
        return $this;
    }

    public function getPageArchiveMembershipDirectories(): ?string
    {
        return $this->pageArchiveMembershipDirectories;
    }

    public function setPageArchiveMembershipDirectories(?string $pageArchiveMembershipDirectories): self
    {
        $this->pageArchiveMembershipDirectories = $pageArchiveMembershipDirectories;
        return $this;
    }

    public function getPageArchiveClubJournals(): ?string
    {
        return $this->pageArchiveClubJournals;
    }

    public function setPageArchiveClubJournals(?string $pageArchiveClubJournals): self
    {
        $this->pageArchiveClubJournals = $pageArchiveClubJournals;
        return $this;
    }
}
