<?php

declare(strict_types=1);

namespace Manuxi\SuluArchiveBundle\Tests\Unit\Content;

use Manuxi\SuluArchiveBundle\Content\ArchiveSmartContentProvider;
use PHPUnit\Framework\TestCase;
use Sulu\Content\Domain\Model\DimensionContentInterface;

class ArchiveSmartContentProviderTest extends TestCase
{
    public function testLiveRowWithoutWorkflowPlaceIsNotShownAsDraft(): void
    {
        // Published in Sulu 3: the live row has no workflowPlace, only workflowPublished.
        $item = $this->mapItem(
            ['workflowPlace' => null, 'workflowPublished' => new \DateTimeImmutable('2026-10-01')],
            DimensionContentInterface::STAGE_LIVE
        );

        $this->assertTrue($item['publishedState']);
        $this->assertNotNull($item['published']);
    }

    public function testLiveRowMigratedFromSuluTwoIsNotShownAsDraft(): void
    {
        $item = $this->mapItem(
            ['workflowPlace' => 'published', 'workflowPublished' => new \DateTimeImmutable('2026-10-01')],
            DimensionContentInterface::STAGE_LIVE
        );

        $this->assertTrue($item['publishedState']);
    }

    public function testDraftRowWithoutPublicationIsShownAsDraft(): void
    {
        $item = $this->mapItem(
            ['workflowPlace' => 'draft', 'workflowPublished' => null],
            DimensionContentInterface::STAGE_DRAFT
        );

        $this->assertFalse($item['publishedState']);
        $this->assertNull($item['published']);
    }

    public function testDraftRowOfPublishedArchiveIsPublishedState(): void
    {
        $item = $this->mapItem(
            ['workflowPlace' => 'draft', 'workflowPublished' => new \DateTimeImmutable('2026-10-01')],
            DimensionContentInterface::STAGE_DRAFT
        );

        $this->assertTrue($item['publishedState']);
    }

    public function testItemFieldsAreMapped(): void
    {
        $item = $this->mapItem(['id' => 'abc', 'title' => 'Minutes'], DimensionContentInterface::STAGE_LIVE);

        $this->assertSame('abc', $item['id']);
        $this->assertSame('Minutes', $item['title']);
    }

    /**
     * @param array<string, mixed> $overrides
     *
     * @return array<string, mixed>
     */
    private function mapItem(array $overrides, string $stage): array
    {
        $item = \array_merge(['id' => 'uuid-1', 'title' => 'Title'], $overrides);

        $reflection = new \ReflectionClass(ArchiveSmartContentProvider::class);
        $provider = $reflection->newInstanceWithoutConstructor();
        $method = $reflection->getMethod('mapQueryResultItem');

        return $method->invoke($provider, $item, $stage);
    }
}
