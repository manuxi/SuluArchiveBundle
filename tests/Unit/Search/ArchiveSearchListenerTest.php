<?php

declare(strict_types=1);

namespace Manuxi\SuluArchiveBundle\Tests\Unit\Search;

use CmsIg\Seal\EngineInterface;
use Manuxi\SuluArchiveBundle\Domain\Event\Archive\ModifiedEvent;
use Manuxi\SuluArchiveBundle\Domain\Event\Archive\PublishedEvent;
use Manuxi\SuluArchiveBundle\Entity\Archive;
use Manuxi\SuluArchiveBundle\Entity\ArchiveDimensionContent;
use Manuxi\SuluArchiveBundle\Search\ArchiveSearchListener;
use PHPUnit\Framework\TestCase;
use Sulu\Component\Localization\Localization;
use Sulu\Component\Webspace\Manager\WebspaceCollection;
use Sulu\Component\Webspace\Manager\WebspaceManagerInterface;
use Sulu\Component\Webspace\Webspace;
use Sulu\Content\Application\ContentAggregator\ContentAggregatorInterface;
use Sulu\Content\Domain\Model\DimensionContentInterface;

class ArchiveSearchListenerTest extends TestCase
{
    /** @var array<string, array<string, mixed>> saved documents per index */
    private array $saved = [];

    public function testPublishedLiveRowWithoutWorkflowPlaceIsIndexedAsPublished(): void
    {
        // Sulu 3 leaves workflowPlace NULL on the live row.
        $archive = $this->createArchive();
        $listener = $this->createListener(
            $this->createDimensionContent($archive, DimensionContentInterface::STAGE_LIVE, null)
        );
        $listener->onPublished(new PublishedEvent($archive));

        $this->assertSame('1', $this->saved['admin']['published']);
        $this->assertArrayHasKey('website', $this->saved);
    }

    public function testPublishedLiveRowMigratedFromSuluTwoIsIndexedAsPublished(): void
    {
        $archive = $this->createArchive();
        $listener = $this->createListener(
            $this->createDimensionContent($archive, DimensionContentInterface::STAGE_LIVE, 'published')
        );
        $listener->onPublished(new PublishedEvent($archive));

        $this->assertSame('1', $this->saved['admin']['published']);
    }

    public function testDraftRowIsIndexedAsDraftUnlessItsWorkflowPlaceIsPublished(): void
    {
        $archive = $this->createArchive();

        $listener = $this->createListener(
            $this->createDimensionContent($archive, DimensionContentInterface::STAGE_DRAFT, 'draft')
        );
        $listener->onCreatedOrModified(new ModifiedEvent($archive));
        $this->assertSame('0', $this->saved['admin']['published']);

        $listener = $this->createListener(
            $this->createDimensionContent($archive, DimensionContentInterface::STAGE_DRAFT, 'published')
        );
        $listener->onCreatedOrModified(new ModifiedEvent($archive));
        $this->assertSame('1', $this->saved['admin']['published']);
    }

    private function createArchive(): Archive
    {
        $archive = $this->createStub(Archive::class);
        $archive->method('getId')->willReturn('uuid-1');
        $archive->method('getUuid')->willReturn('uuid-1');

        return $archive;
    }

    private function createDimensionContent(Archive $archive, string $stage, ?string $workflowPlace): ArchiveDimensionContent
    {
        $dimensionContent = new ArchiveDimensionContent($archive);
        $dimensionContent->setStage($stage);
        $dimensionContent->setTitle('Minutes');
        $dimensionContent->setWorkflowPlace($workflowPlace);

        return $dimensionContent;
    }

    private function createListener(ArchiveDimensionContent $dimensionContent): ArchiveSearchListener
    {
        $this->saved = [];
        $engine = $this->createStub(EngineInterface::class);
        $engine->method('saveDocument')->willReturnCallback(
            function (string $index, array $document): void {
                $this->saved[$index] = $document;
            }
        );

        $webspace = new Webspace();
        $webspace->setLocalizations([new Localization('de')]);
        $webspaceManager = $this->createStub(WebspaceManagerInterface::class);
        $webspaceManager->method('getWebspaceCollection')->willReturn(new WebspaceCollection(['example' => $webspace]));

        $aggregator = $this->createStub(ContentAggregatorInterface::class);
        $aggregator->method('aggregate')->willReturn($dimensionContent);

        return new ArchiveSearchListener($engine, $webspaceManager, $aggregator);
    }
}
