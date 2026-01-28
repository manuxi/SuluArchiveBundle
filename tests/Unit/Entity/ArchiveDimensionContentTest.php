<?php

declare(strict_types=1);

namespace Manuxi\SuluArchiveBundle\Tests\Unit\Entity;

use Manuxi\SuluArchiveBundle\Entity\Archive;
use Manuxi\SuluArchiveBundle\Entity\ArchiveDimensionContent;
use PHPUnit\Framework\TestCase;
use Sulu\Bundle\ContactBundle\Entity\ContactInterface;
use Sulu\Bundle\MediaBundle\Entity\MediaInterface;
use Sulu\Content\Domain\Model\DimensionContentInterface;
use Sulu\Content\Domain\Model\WorkflowInterface;

class ArchiveDimensionContentTest extends TestCase
{
    public function testCopyAttributesFrom(): void
    {
        $archive = $this->createMock(Archive::class);
        $source = new ArchiveDimensionContent($archive);

        // Set source data
        $source->setType('document');
        $source->setTitle('Title');
        $source->setSubtitle('Subtitle');
        $source->setSummary('Summary');
        $source->setText('Text');
        $source->setFooter('Footer');

        $image = $this->createMock(MediaInterface::class);
        $source->setImage($image);
        $source->setImages([$image]);

        $document = $this->createMock(MediaInterface::class);
        $source->setDocument($document);

        $source->setShowAuthor(true);
        $source->setShowDate(true);

        $source->setWorkflowPlace(WorkflowInterface::WORKFLOW_PLACE_PUBLISHED);
        $source->setWorkflowPublished(new \DateTimeImmutable('2023-01-03'));

        // Target
        $target = new ArchiveDimensionContent($archive);
        $target->copyAttributesFrom($source);

        // Assertions
        $this->assertSame($source->getType(), $target->getType());
        $this->assertSame($source->getTitle(), $target->getTitle());
        $this->assertSame($source->getSubtitle(), $target->getSubtitle());
        $this->assertSame($source->getSummary(), $target->getSummary());
        $this->assertSame($source->getText(), $target->getText());
        $this->assertSame($source->getFooter(), $target->getFooter());

        $this->assertSame($source->getImage(), $target->getImage());
        $this->assertSame($source->getImages(), $target->getImages());
        $this->assertSame($source->getDocument(), $target->getDocument());

        $this->assertSame($source->getShowAuthor(), $target->getShowAuthor());
        $this->assertSame($source->getShowDate(), $target->getShowDate());

        $this->assertSame($source->getWorkflowPlace(), $target->getWorkflowPlace());
        $this->assertSame($source->getWorkflowPublished(), $target->getWorkflowPublished());
    }

    public function testSetTemplateData(): void
    {
        $archive = $this->createMock(Archive::class);
        $content = new ArchiveDimensionContent($archive);

        $image = $this->createMock(MediaInterface::class);
        $document = $this->createMock(MediaInterface::class);

        $data = [
            'type' => 'article',
            'title' => 'Archive Title',
            'subtitle' => 'Archive Subtitle',
            'summary' => 'Summary content',
            'text' => 'Main text content',
            'footer' => 'Footer info',
            'images' => [$image],
            'image' => $image,
            'document' => $document,
            'showAuthor' => true,
            'showDate' => false,
        ];

        $content->setTemplateData($data);

        $this->assertEquals('article', $content->getType());
        $this->assertEquals('Archive Title', $content->getTitle());
        $this->assertEquals('Archive Subtitle', $content->getSubtitle());
        $this->assertEquals('Summary content', $content->getSummary());
        $this->assertEquals('Main text content', $content->getText());
        $this->assertEquals('Footer info', $content->getFooter());

        $this->assertCount(1, $content->getImages());
        $this->assertSame($image, $content->getImage());
        $this->assertSame($document, $content->getDocument());

        $this->assertTrue($content->getShowAuthor());
        $this->assertFalse($content->getShowDate());
    }

    public function testGetSetType(): void
    {
        $archive = $this->createMock(Archive::class);
        $content = new ArchiveDimensionContent($archive);
        $this->assertEquals('default', $content->getType());
        $this->assertSame($content, $content->setType('test'));
        $this->assertEquals('test', $content->getType());
    }

    public function testGetSetTitle(): void
    {
        $archive = $this->createMock(Archive::class);
        $content = new ArchiveDimensionContent($archive);
        $this->assertNull($content->getTitle());
        $this->assertSame($content, $content->setTitle('Title'));
        $this->assertEquals('Title', $content->getTitle());
    }

    public function testGetSetSubtitle(): void
    {
        $archive = $this->createMock(Archive::class);
        $content = new ArchiveDimensionContent($archive);
        $this->assertNull($content->getSubtitle());
        $this->assertSame($content, $content->setSubtitle('Subtitle'));
        $this->assertEquals('Subtitle', $content->getSubtitle());
    }

    public function testGetSetSummary(): void
    {
        $archive = $this->createMock(Archive::class);
        $content = new ArchiveDimensionContent($archive);
        $this->assertNull($content->getSummary());
        $this->assertSame($content, $content->setSummary('Summary'));
        $this->assertEquals('Summary', $content->getSummary());
    }

    public function testGetSetText(): void
    {
        $archive = $this->createMock(Archive::class);
        $content = new ArchiveDimensionContent($archive);
        $this->assertNull($content->getText());
        $this->assertSame($content, $content->setText('Text'));
        $this->assertEquals('Text', $content->getText());
    }

    public function testGetSetFooter(): void
    {
        $archive = $this->createMock(Archive::class);
        $content = new ArchiveDimensionContent($archive);
        $this->assertNull($content->getFooter());
        $this->assertSame($content, $content->setFooter('Footer'));
        $this->assertEquals('Footer', $content->getFooter());
    }

    public function testGetSetImage(): void
    {
        $archive = $this->createMock(Archive::class);
        $content = new ArchiveDimensionContent($archive);
        $image = $this->createMock(MediaInterface::class);
        $this->assertNull($content->getImage());
        $this->assertSame($content, $content->setImage($image));
        $this->assertSame($image, $content->getImage());
    }

    public function testGetSetImages(): void
    {
        $archive = $this->createMock(Archive::class);
        $content = new ArchiveDimensionContent($archive);
        $images = [$this->createMock(MediaInterface::class)];
        $this->assertEmpty($content->getImages());
        $this->assertSame($content, $content->setImages($images));
        $this->assertSame($images, $content->getImages());
    }

    public function testGetSetDocument(): void
    {
        $archive = $this->createMock(Archive::class);
        $content = new ArchiveDimensionContent($archive);
        $document = $this->createMock(MediaInterface::class);
        $this->assertNull($content->getDocument());
        $this->assertSame($content, $content->setDocument($document));
        $this->assertSame($document, $content->getDocument());
    }

    public function testGetSetShowAuthor(): void
    {
        $archive = $this->createMock(Archive::class);
        $content = new ArchiveDimensionContent($archive);
        $this->assertFalse($content->getShowAuthor());
        $this->assertSame($content, $content->setShowAuthor(true));
        $this->assertTrue($content->getShowAuthor());
    }

    public function testGetSetShowDate(): void
    {
        $archive = $this->createMock(Archive::class);
        $content = new ArchiveDimensionContent($archive);
        $this->assertFalse($content->getShowDate());
        $this->assertSame($content, $content->setShowDate(true));
        $this->assertTrue($content->getShowDate());
    }

    public function testGetResource(): void
    {
        $archive = $this->createMock(Archive::class);
        $content = new ArchiveDimensionContent($archive);
        $this->assertSame($archive, $content->getResource());
    }

    public function testGetArchive(): void
    {
        $archive = $this->createMock(Archive::class);
        $content = new ArchiveDimensionContent($archive);
        $this->assertSame($archive, $content->getArchive());
    }

    public function testGetTemplateType(): void
    {
        $this->assertEquals(Archive::TEMPLATE_TYPE, ArchiveDimensionContent::getTemplateType());
    }

    public function testGetResourceKey(): void
    {
        $this->assertEquals(Archive::RESOURCE_KEY, ArchiveDimensionContent::getResourceKey());
    }

    public function testGetSetWorkflowPlace(): void
    {
        $archive = $this->createMock(Archive::class);
        $content = new ArchiveDimensionContent($archive);
        $this->assertNull($content->getWorkflowPlace());
        $content->setWorkflowPlace('published');
        $this->assertEquals('published', $content->getWorkflowPlace());
    }

    public function testGetSetWorkflowPublished(): void
    {
        $archive = $this->createMock(Archive::class);
        $content = new ArchiveDimensionContent($archive);
        $date = new \DateTimeImmutable();
        $this->assertNull($content->getWorkflowPublished());
        $content->setWorkflowPublished($date);
        $this->assertSame($date, $content->getWorkflowPublished());
    }

    public function testGetSetLocale(): void
    {
        $archive = $this->createMock(Archive::class);
        $content = new ArchiveDimensionContent($archive);
        $this->assertNull($content->getLocale());
        $content->setLocale('en');
        $this->assertEquals('en', $content->getLocale());
    }

    public function testGetSetStage(): void
    {
        $archive = $this->createMock(Archive::class);
        $content = new ArchiveDimensionContent($archive);
        $this->assertEquals(DimensionContentInterface::STAGE_DRAFT, $content->getStage());
        $content->setStage('live');
        $this->assertEquals('live', $content->getStage());
    }

    public function testGetSetVersion(): void
    {
        $archive = $this->createMock(Archive::class);
        $content = new ArchiveDimensionContent($archive);

        $content->setVersion(2);
        $this->assertEquals(2, $content->getVersion());
    }

    public function testGetSetAuthor(): void
    {
        $archive = $this->createMock(Archive::class);
        $content = new ArchiveDimensionContent($archive);
        $author = $this->createMock(ContactInterface::class);

        $this->assertNull($content->getAuthor());
        $content->setAuthor($author);
        $this->assertSame($author, $content->getAuthor());
    }

    public function testGetSetAuthored(): void
    {
        $archive = $this->createMock(Archive::class);
        $content = new ArchiveDimensionContent($archive);
        $date = new \DateTimeImmutable();

        $content->setAuthored($date);
        $this->assertSame($date, $content->getAuthored());
    }
}
