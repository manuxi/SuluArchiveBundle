<?php

declare(strict_types=1);

namespace Manuxi\SuluArchiveBundle\Tests\Unit\Entity;

use Manuxi\SuluArchiveBundle\Entity\Archive;
use Manuxi\SuluArchiveBundle\Entity\ArchiveDimensionContent;
use PHPUnit\Framework\TestCase;
use Sulu\Content\Domain\Model\DimensionContentInterface;

class ArchiveTest extends TestCase
{
    private Archive $entity;

    protected function setUp(): void
    {
        $this->entity = new Archive();
    }

    public function testGetIdReturnsUuidForNewEntity(): void
    {
        $id = $this->entity->getId();
        $this->assertIsString($id);
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/',
            $id
        );
    }

    public function testGetUuidReturnsUuid(): void
    {
        $uuid = $this->entity->getUuid();
        $this->assertIsString($uuid);
        $this->assertSame($this->entity->getId(), $uuid);
    }

    public function testGetDimensionContentsReturnsEmptyCollectionForNewEntity(): void
    {
        $dimensionContents = $this->entity->getDimensionContents();

        $this->assertInstanceOf(\Doctrine\Common\Collections\Collection::class, $dimensionContents);
        $this->assertCount(0, $dimensionContents);
    }

    public function testAddDimensionContentAddsToDimensionContents(): void
    {
        $dimensionContent = new ArchiveDimensionContent($this->entity);
        $dimensionContent->setLocale('en');
        $dimensionContent->setStage(DimensionContentInterface::STAGE_DRAFT);

        $this->entity->addDimensionContent($dimensionContent);

        $dimensionContents = $this->entity->getDimensionContents();
        $this->assertCount(1, $dimensionContents);
        $this->assertTrue($dimensionContents->contains($dimensionContent));
    }

    public function testRemoveDimensionContentRemovesFromDimensionContents(): void
    {
        $dimensionContent = new ArchiveDimensionContent($this->entity);
        $dimensionContent->setLocale('en');
        $dimensionContent->setStage(DimensionContentInterface::STAGE_DRAFT);

        $this->entity->addDimensionContent($dimensionContent);
        $this->assertCount(1, $this->entity->getDimensionContents());

        $this->entity->removeDimensionContent($dimensionContent);
        $this->assertCount(0, $this->entity->getDimensionContents());
    }

    public function testCreateDimensionContentCreatesNewDimensionContent(): void
    {
        $dimensionContent = $this->entity->createDimensionContent();

        $this->assertInstanceOf(ArchiveDimensionContent::class, $dimensionContent);
        $this->assertSame($this->entity, $dimensionContent->getResource());
    }

    public function testGetResourceKeyReturnsCorrectValue(): void
    {
        $this->assertEquals('archives', Archive::RESOURCE_KEY);
    }

    public function testGetSecurityContextReturnsCorrectValue(): void
    {
        $this->assertEquals('sulu.archives.archives', Archive::SECURITY_CONTEXT);
    }
}
