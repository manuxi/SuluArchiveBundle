<?php

declare(strict_types=1);

namespace Manuxi\SuluArchiveBundle\Tests\Unit\Content\Type;

use Manuxi\SuluArchiveBundle\Content\Type\SingleArchiveSelection;
use Manuxi\SuluArchiveBundle\Entity\Archive;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Sulu\Component\Content\Compat\PropertyInterface;

class SingleArchiveSelectionTest extends TestCase
{
    private SingleArchiveSelection $singleArchiveSelection;

    private ObjectProphecy $archiveRepository;

    protected function setUp(): void
    {
        $this->archiveRepository = $this->prophesize(ObjectRepository::class);
        $entityManager         = $this->prophesize(EntityManagerInterface::class);
        $entityManager->getRepository(Archive::class)->willReturn($this->archiveRepository->reveal());

        $this->singleArchiveSelection = new SingleArchiveSelection($entityManager->reveal());
    }

    public function testNullValue(): void
    {
        $property = $this->prophesize(PropertyInterface::class);
        $property->getValue()->willReturn(null);

        $this->assertNull($this->singleArchiveSelection->getContentData($property->reveal()));
        $this->assertSame(['id' => null], $this->singleArchiveSelection->getViewData($property->reveal()));
    }

    public function testValidValue(): void
    {
        $property = $this->prophesize(PropertyInterface::class);
        $property->getValue()->willReturn(45);

        $archive45 = $this->prophesize(Archive::class);

        $this->archiveRepository->find(45)->willReturn($archive45->reveal());

        $this->assertSame($archive45->reveal(), $this->singleArchiveSelection->getContentData($property->reveal()));
        $this->assertSame(['id' => 45], $this->singleArchiveSelection->getViewData($property->reveal()));
    }
}
