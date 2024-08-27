<?php

declare(strict_types=1);

namespace Manuxi\SuluArchiveBundle\Tests\Unit\Entity;

use Manuxi\SuluArchiveBundle\Entity\Archive;
use Manuxi\SuluArchiveBundle\Entity\ArchiveExcerpt;
use Manuxi\SuluArchiveBundle\Entity\ArchiveSeo;
use Manuxi\SuluArchiveBundle\Entity\ArchiveTranslation;
use Manuxi\SuluArchiveBundle\Entity\Location;
use DateTimeImmutable;
use Prophecy\Prophecy\ObjectProphecy;
use Sulu\Bundle\MediaBundle\Entity\MediaInterface;
use Sulu\Bundle\TestBundle\Testing\SuluTestCase;

class ArchiveTest extends SuluTestCase
{
    private Archive $entity;
    private string $testString = "Lorem ipsum dolor sit amet, ...";

    protected function setUp(): void
    {
        $this->entity = new Archive();
        $this->entity->setLocale('de');
    }

    public function testImage(): void
    {
        $image = $this->prophesize(MediaInterface::class);
        $image->getId()->willReturn(42);

        $this->assertNull($this->entity->getImage());
        $this->assertNull($this->entity->getImageData());
        $this->assertSame($this->entity, $this->entity->setImage($image->reveal()));
        $this->assertSame($image->reveal(), $this->entity->getImage());
        $this->assertSame(['id' => 42], $this->entity->getImageData());
    }

    public function testTitle(): void
    {
        $this->assertNull($this->entity->getTitle());
        $this->assertSame($this->entity, $this->entity->setTitle($this->testString));
        $this->assertSame($this->testString, $this->entity->getTitle());

        $this->assertInstanceOf(ArchiveTranslation::class, $this->entity->getTranslations()['de']);
        $this->assertSame('de', $this->entity->getTranslations()['de']->getLocale());
        $this->assertSame($this->testString, $this->entity->getTranslations()['de']->getTitle());
    }

    public function testSubtitle(): void
    {
        $this->assertNull($this->entity->getSubtitle());
        $this->assertSame($this->entity, $this->entity->setSubtitle($this->testString));
        $this->assertSame($this->testString, $this->entity->getSubtitle());

        $this->assertInstanceOf(ArchiveTranslation::class, $this->entity->getTranslations()['de']);
        $this->assertSame('de', $this->entity->getTranslations()['de']->getLocale());
        $this->assertSame($this->testString, $this->entity->getTranslations()['de']->getSubtitle());
    }

    public function testSummary(): void
    {
        $this->assertNull($this->entity->getSummary());
        $this->assertSame($this->entity, $this->entity->setSummary($this->testString));
        $this->assertSame($this->testString, $this->entity->getSummary());

        $this->assertInstanceOf(ArchiveTranslation::class, $this->entity->getTranslations()['de']);
        $this->assertSame('de', $this->entity->getTranslations()['de']->getLocale());
        $this->assertSame($this->testString, $this->entity->getTranslations()['de']->getSummary());
    }

    public function testText(): void
    {
        $this->assertNull($this->entity->getText());
        $this->assertSame($this->entity, $this->entity->setText($this->testString));
        $this->assertSame($this->testString, $this->entity->getText());

        $this->assertInstanceOf(ArchiveTranslation::class, $this->entity->getTranslations()['de']);
        $this->assertSame('de', $this->entity->getTranslations()['de']->getLocale());
        $this->assertSame($this->testString, $this->entity->getTranslations()['de']->getText());
    }

    public function testFooter(): void
    {
        $this->assertNull($this->entity->getFooter());
        $this->assertSame($this->entity, $this->entity->setFooter($this->testString));
        $this->assertSame($this->testString, $this->entity->getFooter());

        $this->assertInstanceOf(ArchiveTranslation::class, $this->entity->getTranslations()['de']);
        $this->assertSame('de', $this->entity->getTranslations()['de']->getLocale());
        $this->assertSame($this->testString, $this->entity->getTranslations()['de']->getFooter());
    }

    public function testRoutePath(): void
    {
        $testRoutePath = 'entities/entity-100';

        $this->assertNull($this->entity->getRoutePath());
        $this->assertSame($this->entity, $this->entity->setRoutePath($testRoutePath));
        $this->assertSame($testRoutePath, $this->entity->getRoutePath());

        $this->assertInstanceOf(ArchiveTranslation::class, $this->entity->getTranslations()['de']);
        $this->assertSame('de', $this->entity->getTranslations()['de']->getLocale());
        $this->assertSame($testRoutePath, $this->entity->getTranslations()['de']->getRoutePath());
    }

    public function testLocale(): void
    {
        $this->assertSame('de', $this->entity->getLocale());
        $this->assertSame($this->entity, $this->entity->setLocale('en'));
        $this->assertSame('en', $this->entity->getLocale());
    }

    public function testArchiveSeo(): void
    {
        $entitySeo = $this->prophesize(ArchiveSeo::class);
        $entitySeo->getId()->willReturn(42);

        $this->assertInstanceOf(ArchiveSeo::class, $this->entity->getArchiveSeo());
        $this->assertNull($this->entity->getArchiveSeo()->getId());
        $this->assertSame($this->entity, $this->entity->setArchiveSeo($entitySeo->reveal()));
        $this->assertSame($entitySeo->reveal(), $this->entity->getArchiveSeo());
    }

    public function testArchiveExcerpt(): void
    {
        $entityExcerpt = $this->prophesize(ArchiveExcerpt::class);
        $entityExcerpt->getId()->willReturn(42);

        $this->assertInstanceOf(ArchiveExcerpt::class, $this->entity->getArchiveExcerpt());
        $this->assertNull($this->entity->getArchiveExcerpt()->getId());
        $this->assertSame($this->entity, $this->entity->setArchiveExcerpt($entityExcerpt->reveal()));
        $this->assertSame($entityExcerpt->reveal(), $this->entity->getArchiveExcerpt());
    }

    public function testExt(): void
    {
        $ext = $this->entity->getExt();
        $this->assertArrayHasKey('seo', $ext);
        $this->assertInstanceOf(ArchiveSeo::class, $ext['seo']);
        $this->assertNull($ext['seo']->getId());

        $this->assertArrayHasKey('excerpt', $ext);
        $this->assertInstanceOf(ArchiveExcerpt::class, $ext['excerpt']);
        $this->assertNull($ext['excerpt']->getId());

        $this->entity->addExt('foo', new ArchiveSeo());
        $this->entity->addExt('bar', new ArchiveExcerpt());
        $ext = $this->entity->getExt();

        $this->assertArrayHasKey('seo', $ext);
        $this->assertInstanceOf(ArchiveSeo::class, $ext['seo']);
        $this->assertNull($ext['seo']->getId());

        $this->assertArrayHasKey('excerpt', $ext);
        $this->assertInstanceOf(ArchiveExcerpt::class, $ext['excerpt']);
        $this->assertNull($ext['excerpt']->getId());

        $this->assertArrayHasKey('foo', $ext);
        $this->assertInstanceOf(ArchiveSeo::class, $ext['foo']);
        $this->assertNull($ext['foo']->getId());

        $this->assertArrayHasKey('bar', $ext);
        $this->assertInstanceOf(ArchiveExcerpt::class, $ext['bar']);
        $this->assertNull($ext['bar']->getId());

        $this->assertTrue($this->entity->hasExt('seo'));
        $this->assertTrue($this->entity->hasExt('excerpt'));
        $this->assertTrue($this->entity->hasExt('foo'));
        $this->assertTrue($this->entity->hasExt('bar'));

        $this->entity->setExt(['and' => 'now', 'something' => 'special']);
        $ext = $this->entity->getExt();
        $this->assertArrayNotHasKey('seo', $ext);
        $this->assertArrayNotHasKey('excerpt', $ext);
        $this->assertArrayNotHasKey('foo', $ext);
        $this->assertArrayNotHasKey('bar', $ext);
        $this->assertArrayHasKey('and', $ext);
        $this->assertArrayHasKey('something', $ext);
        $this->assertTrue($this->entity->hasExt('and'));
        $this->assertTrue($this->entity->hasExt('something'));
        $this->assertTrue('now' === $ext['and']);
        $this->assertTrue('special' === $ext['something']);
    }

    public function testPropagateLocale(): void
    {
        $this->assertSame($this->entity->getExt()['seo']->getLocale(), 'de');
        $this->assertSame($this->entity->getExt()['excerpt']->getLocale(), 'de');
        $this->entity->setLocale('en');
        $this->assertSame($this->entity->getExt()['seo']->getLocale(), 'en');
        $this->assertSame($this->entity->getExt()['excerpt']->getLocale(), 'en');
    }

    public function testTranslations(): void
    {
        $this->assertSame($this->entity->getTranslations(), []);
        $this->entity->setText($this->testString);
        $this->assertNotSame($this->entity->getTranslations(), []);
        $this->assertArrayHasKey('de', $this->entity->getTranslations());
        $this->assertArrayNotHasKey('en', $this->entity->getTranslations());
        $this->assertSame($this->entity->getText(), $this->testString);

        $this->entity->setLocale('en');
        $this->entity->setText($this->testString);
        $this->assertArrayHasKey('de', $this->entity->getTranslations());
        $this->assertArrayHasKey('en', $this->entity->getTranslations());
        $this->assertSame($this->entity->getText(), $this->testString);
        //No need to test more, it's s already done...
    }
}
