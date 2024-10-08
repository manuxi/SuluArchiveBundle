<?php

declare(strict_types=1);

namespace Manuxi\SuluArchiveBundle\Tests\Unit\Entity;

use DateTime;
use Manuxi\SuluArchiveBundle\Entity\Archive;
use Manuxi\SuluArchiveBundle\Entity\ArchiveTranslation;
use Prophecy\Prophecy\ObjectProphecy;
use Sulu\Bundle\TestBundle\Testing\SuluTestCase;

class ArchiveTranslationTest extends SuluTestCase
{
    private ObjectProphecy $archive;
    private ArchiveTranslation $translation;
    private string $testString = "Lorem ipsum dolor sit amet, ...";

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();
    }

    protected function setUp(): void
    {
        $this->archive       = $this->prophesize(Archive::class);
        $this->translation = new ArchiveTranslation($this->archive->reveal(), 'de');
    }

    public function testArchive(): void
    {
        $this->assertSame($this->archive->reveal(), $this->translation->getArchive());
    }

    public function testLocale(): void
    {
        $this->assertSame('de', $this->translation->getLocale());
    }

    public function testTitle(): void
    {
        $this->assertNull($this->translation->getTitle());
        $this->assertSame($this->translation, $this->translation->setTitle($this->testString));
        $this->assertSame($this->testString, $this->translation->getTitle());
    }

    public function testSubtitle(): void
    {
        $this->assertNull($this->translation->getSubtitle());
        $this->assertSame($this->translation, $this->translation->setSubtitle($this->testString));
        $this->assertSame($this->testString, $this->translation->getSubtitle());
    }

    public function testSummary(): void
    {
        $this->assertNull($this->translation->getSummary());
        $this->assertSame($this->translation, $this->translation->setSummary($this->testString));
        $this->assertSame($this->testString, $this->translation->getSummary());
    }

    public function testText(): void
    {
        $this->assertNull($this->translation->getText());
        $this->assertSame($this->translation, $this->translation->setText($this->testString));
        $this->assertSame($this->testString, $this->translation->getText());
    }

    public function testFooter(): void
    {
        $this->assertNull($this->translation->getFooter());
        $this->assertSame($this->translation, $this->translation->setFooter($this->testString));
        $this->assertSame($this->testString, $this->translation->getFooter());
    }

    public function testRoutePath(): void
    {
        $testRoutePath = 'archive/archive-100';
        $this->assertEmpty($this->translation->getRoutePath());
        $this->assertSame($this->translation, $this->translation->setRoutePath($testRoutePath));
        $this->assertSame($testRoutePath, $this->translation->getRoutePath());
    }

    public function testPublished(): void
    {
        $this->assertFalse($this->translation->isPublished());
        $this->assertSame($this->translation, $this->translation->setPublished(true));
        $this->assertTrue($this->translation->isPublished());
        $this->assertSame($this->translation, $this->translation->setPublished(false));
        $this->assertFalse($this->translation->isPublished());
    }

    public function testPublishedAt(): void
    {
        $this->assertNull($this->translation->getPublishedAt());
        $this->assertSame($this->translation, $this->translation->setPublished(true));
        $this->assertNotNull($this->translation->getPublishedAt());
        $this->assertSame(DateTime::class, get_class($this->translation->getPublishedAt()));
        $this->assertSame($this->translation, $this->translation->setPublished(false));
        $this->assertNull($this->translation->getPublishedAt());
    }

}
