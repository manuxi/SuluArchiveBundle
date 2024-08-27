<?php

declare(strict_types=1);

namespace Manuxi\SuluArchiveBundle\Tests\Unit\Content\Type;

use Manuxi\SuluArchiveBundle\Content\Type\ArchiveSelection;
use Manuxi\SuluArchiveBundle\Entity\Archive;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Sulu\Component\Content\Compat\PropertyInterface;

class ArchiveSelectionTest extends TestCase
{
    private ArchiveSelection $archiveSelection;
    private ObjectProphecy $archiveRepository;

    protected function setUp(): void
    {
        $this->archiveRepository = $this->prophesize(ObjectRepository::class);
        $entityManager         = $this->prophesize(EntityManagerInterface::class);
        $entityManager->getRepository(Archive::class)->willReturn($this->archiveRepository->reveal());

        $this->archiveSelection = new ArchiveSelection($entityManager->reveal());
    }

    public function testNullValue(): void
    {
        $property = $this->prophesize(PropertyInterface::class);
        $property->getValue()->willReturn(null);

        $this->assertSame([], $this->archiveSelection->getContentData($property->reveal()));
        $this->assertSame(['ids' => null], $this->archiveSelection->getViewData($property->reveal()));
    }

    public function testEmptyArrayValue(): void
    {
        $property = $this->prophesize(PropertyInterface::class);
        $property->getValue()->willReturn([]);

        $this->assertSame([], $this->archiveSelection->getContentData($property->reveal()));
        $this->assertSame(['ids' => []], $this->archiveSelection->getViewData($property->reveal()));
    }

    public function testValidValue(): void
    {
        $property = $this->prophesize(PropertyInterface::class);
        $property->getValue()->willReturn([45, 22]);

        $archive22 = $this->prophesize(Archive::class);
        $archive22->getId()->willReturn(22);

        $archive45 = $this->prophesize(Archive::class);
        $archive45->getId()->willReturn(45);

        $this->archiveRepository->findBy(['id' => [45, 22]])->willReturn([
            $archive22->reveal(),
            $archive45->reveal(),
        ]);

        $this->assertSame(
            [
                $archive45->reveal(),
                $archive22->reveal(),
            ],
            $this->archiveSelection->getContentData($property->reveal())
        );
        $this->assertSame(['ids' => [45, 22]], $this->archiveSelection->getViewData($property->reveal()));
    }
}
