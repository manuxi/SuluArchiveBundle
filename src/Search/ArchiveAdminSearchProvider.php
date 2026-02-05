<?php
declare(strict_types=1);
namespace Manuxi\SuluArchiveBundle\Search;
use CmsIg\Seal\Reindex\ReindexConfig;
use CmsIg\Seal\Reindex\ReindexProviderInterface;
use Manuxi\SuluArchiveBundle\Entity\Archive;
use Manuxi\SuluArchiveBundle\Entity\ArchiveDimensionContent;
use Manuxi\SuluArchiveBundle\Repository\ArchiveRepository;
use Sulu\Component\Webspace\Manager\WebspaceManagerInterface;
use Sulu\Content\Application\ContentAggregator\ContentAggregatorInterface;
use Sulu\Content\Domain\Model\DimensionContentInterface;
class ArchiveAdminSearchProvider implements ReindexProviderInterface
{
    public function __construct(
        private readonly ArchiveRepository $archiveRepository,
        private readonly WebspaceManagerInterface $webspaceManager,
        private readonly ContentAggregatorInterface $contentAggregator,
    ) {
    }
    public static function getIndex(): string
    {
        return 'admin';
    }
    public function total(): ?int
    {
        return $this->archiveRepository->countAll();
    }
    public function provide(ReindexConfig $reindexConfig): \Generator
    {
        $locales = $this->getLocales();
        foreach ($locales as $locale) {
            $archives = $this->archiveRepository->findAll();
            foreach ($archives as $archive) {
                /** @var ArchiveDimensionContent $dimensionContent */
                $dimensionContent = $this->contentAggregator->aggregate(
                    $archive,
                    [
                        'locale' => $locale,
                        'stage' => DimensionContentInterface::STAGE_DRAFT,
                        'version' => DimensionContentInterface::CURRENT_VERSION,
                    ]
                );
                // Skip if no content for this locale
                if (!$dimensionContent->getTitle()) {
                    continue;
                }
                yield $this->createDocument($archive, $dimensionContent, $locale);
            }
        }
    }
    private function getLocales(): array
    {
        $locales = [];
        foreach ($this->webspaceManager->getWebspaceCollection() as $webspace) {
            foreach ($webspace->getAllLocalizations() as $localization) {
                $locales[$localization->getLocale()] = true;
            }
        }
        return array_keys($locales);
    }
    private function createDocument(Archive $archive, ArchiveDimensionContent $dimensionContent, string $locale): array
    {
        $content = array_filter([
            $dimensionContent->getSubtitle(),
            $dimensionContent->getSummary(),
            $dimensionContent->getText(),
            $dimensionContent->getFooter(),
        ]);
        return [
            'id' => 'archive-' . $archive->getId() . '-' . $locale . '-draft',
            'resourceKey' => Archive::RESOURCE_KEY,
            'resourceId' => (string) $archive->getId(),
            'locale' => $locale,
            'securityContext' => Archive::SECURITY_CONTEXT,
            'webspaces' => [],
            'title' => $dimensionContent->getTitle() ?? '',
            'url' => $dimensionContent->getRoute()?->getSlug() ?? '',
            'content' => array_values($content),
            'type' => $dimensionContent->getType(),
            'mediaId' => $dimensionContent->getImage()?->getId(),
        ];
    }
}
