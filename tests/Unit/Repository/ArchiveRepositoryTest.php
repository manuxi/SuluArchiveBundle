<?php

declare(strict_types=1);

namespace Manuxi\SuluArchiveBundle\Tests\Unit\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Manuxi\SuluArchiveBundle\Entity\Archive;
use Manuxi\SuluArchiveBundle\Repository\ArchiveRepository;
use PHPUnit\Framework\TestCase;

class ArchiveRepositoryTest extends TestCase
{
    public function testCountPublishedDoesNotRequireWorkflowPlaceOnLiveRow(): void
    {
        // Sulu 3 leaves workflowPlace NULL on live rows, a live row alone means "published".
        $queryBuilder = new class(self::createStub(EntityManagerInterface::class)) extends QueryBuilder {
            public function getQuery(): Query
            {
                throw new \RuntimeException('captured');
            }
        };
        $queryBuilder->select('archive')->from(Archive::class, 'archive');

        $repository = $this->getMockBuilder(ArchiveRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['createQueryBuilder'])
            ->getMock();
        $repository->method('createQueryBuilder')->willReturn($queryBuilder);

        try {
            $repository->countPublished('de');
            $this->fail('Query was not built.');
        } catch (\RuntimeException) {
        }

        $dql = $queryBuilder->getDQL();
        $this->assertStringContainsString('dc.stage = :stage', $dql);
        $this->assertStringContainsString('dc.locale = :locale', $dql);
        $this->assertStringNotContainsString('workflowPlace', $dql);

        $parameters = [];
        foreach ($queryBuilder->getParameters() as $parameter) {
            $parameters[$parameter->getName()] = $parameter->getValue();
        }
        $this->assertSame(['locale' => 'de', 'stage' => 'live'], $parameters);
    }
}
