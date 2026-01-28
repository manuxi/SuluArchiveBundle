<?php

declare(strict_types=1);

namespace Manuxi\SuluArchiveBundle\Tests\Unit\Content\PropertyResolver;

use Manuxi\SuluArchiveBundle\Content\PropertyResolver\SingleArchiveSelectionPropertyResolver;
use PHPUnit\Framework\TestCase;
use Sulu\Content\Application\ContentResolver\Value\ContentView;

class SingleArchiveSelectionPropertyResolverTest extends TestCase
{
    private SingleArchiveSelectionPropertyResolver $resolver;

    protected function setUp(): void
    {
        $this->resolver = new SingleArchiveSelectionPropertyResolver();
    }

    public function testResolveReturnsNullContentViewForNullData(): void
    {
        $result = $this->resolver->resolve(null, 'en');

        $this->assertInstanceOf(ContentView::class, $result);
        $this->assertNull($result->getContent());
    }

    public function testResolveReturnsNullContentViewForNonStringData(): void
    {
        $result = $this->resolver->resolve(123, 'en');

        $this->assertInstanceOf(ContentView::class, $result);
        $this->assertNull($result->getContent());
    }

    public function testResolveReturnsResolvableContentViewForValidUuid(): void
    {
        $uuid = '550e8400-e29b-41d4-a716-446655440000';
        $result = $this->resolver->resolve($uuid, 'en');

        $this->assertInstanceOf(ContentView::class, $result);
        $view = $result->getView();
        $this->assertEquals($uuid, $view['id']);
    }

    public function testGetTypeReturnsCorrectType(): void
    {
        $this->assertEquals('single_archive_selection', SingleArchiveSelectionPropertyResolver::getType());
    }
}
