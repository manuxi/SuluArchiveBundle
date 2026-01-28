<?php

declare(strict_types=1);

namespace Manuxi\SuluArchiveBundle\Tests\Unit\Content\ResourceLoader;

use Manuxi\SuluArchiveBundle\Content\ResourceLoader\ArchiveResourceLoader;
use Manuxi\SuluArchiveBundle\Entity\Archive;
use Manuxi\SuluArchiveBundle\Repository\ArchiveRepository;
use PHPUnit\Framework\TestCase;

class ArchiveResourceLoaderTest extends TestCase
{
    private ArchiveResourceLoader $loader;
    private ArchiveRepository $repository;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(ArchiveRepository::class);
        $this->loader = new ArchiveResourceLoader($this->repository);
    }

    public function testGetKeyReturnsCorrectKey(): void
    {
        $this->assertEquals('archives', ArchiveResourceLoader::getKey());
    }

    public function testLoadReturnsArchivesForValidIds(): void
    {
        $archive1 = $this->createMock(Archive::class);
        $archive1->method('getUuid')->willReturn('uuid-1');

        $archive2 = $this->createMock(Archive::class);
        $archive2->method('getUuid')->willReturn('uuid-2');

        $this->repository->expects($this->once())
            ->method('findByUuids')
            ->with(['uuid-1', 'uuid-2'])
            ->willReturn([$archive1, $archive2]);

        $result = $this->loader->load(['uuid-1', 'uuid-2'], 'en', []);

        $this->assertCount(2, $result);
        $this->assertArrayHasKey('uuid-1', $result);
        $this->assertArrayHasKey('uuid-2', $result);
    }

    public function testLoadReturnsEmptyArrayForEmptyIds(): void
    {
        $this->repository->expects($this->never())
            ->method('findByUuids');

        $result = $this->loader->load([], 'en', []);

        $this->assertEmpty($result);
    }
}
