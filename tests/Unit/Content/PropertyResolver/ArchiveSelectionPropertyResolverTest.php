<?php

declare(strict_types=1);

namespace Manuxi\SuluArchiveBundle\Tests\Unit\Content\PropertyResolver;

use Manuxi\SuluArchiveBundle\Content\PropertyResolver\ArchiveSelectionPropertyResolver;
use PHPUnit\Framework\TestCase;
use Sulu\Content\Application\ContentResolver\Value\ContentView;

class ArchiveSelectionPropertyResolverTest extends TestCase
{
    private ArchiveSelectionPropertyResolver $resolver;

    protected function setUp(): void
    {
        $this->resolver = new ArchiveSelectionPropertyResolver();
    }

    public function testResolveReturnsEmptyContentViewForNullData(): void
    {
        $result = $this->resolver->resolve(null, 'en');

        $this->assertInstanceOf(ContentView::class, $result);
        $this->assertEquals([], $result->getContent());
    }

    public function testResolveReturnsEmptyContentViewForEmptyArray(): void
    {
        $result = $this->resolver->resolve([], 'en');

        $this->assertInstanceOf(ContentView::class, $result);
        $this->assertEquals([], $result->getContent());
    }

    public function testResolveReturnsEmptyContentViewForNonArrayData(): void
    {
        $result = $this->resolver->resolve('not-an-array', 'en');

        $this->assertInstanceOf(ContentView::class, $result);
        $this->assertEquals([], $result->getContent());
    }

    public function testResolveReturnsResolvableContentViewForValidIds(): void
    {
        $ids = [
            '550e8400-e29b-41d4-a716-446655440000',
            '550e8400-e29b-41d4-a716-446655440001',
        ];
        $result = $this->resolver->resolve($ids, 'en');

        $this->assertInstanceOf(ContentView::class, $result);
        $view = $result->getView();
        $this->assertEquals($ids, $view['ids']);
    }

    public function testGetTypeReturnsCorrectType(): void
    {
        $this->assertEquals('archive_selection', ArchiveSelectionPropertyResolver::getType());
    }
}
