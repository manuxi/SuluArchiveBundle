<?php

declare(strict_types=1);

namespace Manuxi\SuluArchiveBundle\Tests\Unit\Service;

use Manuxi\SuluArchiveBundle\Service\ArchiveTypeSelect;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

class ArchiveTypeSelectTest extends TestCase
{
    private ArchiveTypeSelect $archiveTypeSelect;
    private TranslatorInterface $translator;

    protected function setUp(): void
    {
        $this->translator = $this->createMock(TranslatorInterface::class);

        $types = [
            'default' => [
                'name' => 'sulu_archive.default',
                'color' => '#0d6efd',
            ],
            'document' => [
                'name' => 'sulu_archive.document',
                'color' => '#ffc107',
            ],
            'article' => [
                'name' => 'sulu_archive.article',
            ],
        ];

        $this->archiveTypeSelect = new ArchiveTypeSelect($this->translator, $types, 'default');
    }

    public function testGetValues(): void
    {
        $this->translator->expects($this->exactly(3))
            ->method('trans')
            ->willReturnCallback(fn($key) => match ($key) {
                'sulu_archive.default' => 'Default',
                'sulu_archive.document' => 'Document',
                'sulu_archive.article' => 'Article',
                default => $key,
            });

        $values = $this->archiveTypeSelect->getValues();

        $this->assertCount(3, $values);
        $this->assertEquals('default', $values[0]['name']);
        $this->assertEquals('Default', $values[0]['title']);
    }

    public function testGetDefaultValue(): void
    {
        $this->assertEquals('default', $this->archiveTypeSelect->getDefaultValue());
    }

    public function testGetColor(): void
    {
        $this->assertEquals('#0d6efd', $this->archiveTypeSelect->getColor('default'));
        $this->assertEquals('#ffc107', $this->archiveTypeSelect->getColor('document'));

        // Fallback to default
        $this->assertEquals('#0d6efd', $this->archiveTypeSelect->getColor('article'));

        // Non-existent type
        $this->assertEquals('#0d6efd', $this->archiveTypeSelect->getColor('unknown'));
    }

    public function testGetTypeName(): void
    {
        $this->translator->expects($this->any())
            ->method('trans')
            ->willReturnMap([
                ['sulu_archive.default', [], 'admin', null, 'Default'],
                ['sulu_archive.document', [], 'admin', null, 'Document'],
            ]);

        $this->assertEquals('Document', $this->archiveTypeSelect->getTypeName('document'));

        // Fallback to default
        $this->assertEquals('Default', $this->archiveTypeSelect->getTypeName('unknown'));
    }

    public function testHasType(): void
    {
        $this->assertTrue($this->archiveTypeSelect->hasType('document'));
        $this->assertFalse($this->archiveTypeSelect->hasType('unknown'));
    }
}
