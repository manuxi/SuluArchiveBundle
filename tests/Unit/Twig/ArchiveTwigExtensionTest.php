<?php

declare(strict_types=1);

namespace Manuxi\SuluArchiveBundle\Tests\Unit\Twig;

use Doctrine\ORM\EntityManagerInterface;
use Manuxi\SuluArchiveBundle\Entity\Archive;
use Manuxi\SuluArchiveBundle\Entity\ArchiveDimensionContent;
use Manuxi\SuluArchiveBundle\Repository\ArchiveRepository;
use Manuxi\SuluArchiveBundle\Twig\ArchiveTwigExtension;
use PHPUnit\Framework\TestCase;
use Sulu\Component\Localization\Localization;
use Sulu\Component\Webspace\Analyzer\RequestAnalyzerInterface;
use Sulu\Content\Application\ContentAggregator\ContentAggregatorInterface;
use Sulu\Content\Application\ContentResolver\ContentResolverInterface;
use Sulu\Content\Domain\Model\DimensionContentInterface;

class ArchiveTwigExtensionTest extends TestCase
{
    private ArchiveTwigExtension $extension;
    private EntityManagerInterface $entityManager;
    private ContentAggregatorInterface $contentAggregator;
    private ContentResolverInterface $contentResolver;
    private RequestAnalyzerInterface $requestAnalyzer;
    private ArchiveRepository $archiveRepository;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->contentAggregator = $this->createMock(ContentAggregatorInterface::class);
        $this->contentResolver = $this->createMock(ContentResolverInterface::class);
        $this->requestAnalyzer = $this->createMock(RequestAnalyzerInterface::class);
        $this->archiveRepository = $this->createMock(ArchiveRepository::class);

        $this->entityManager->method('getRepository')
            ->with(Archive::class)
            ->willReturn($this->archiveRepository);

        $this->extension = new ArchiveTwigExtension(
            $this->entityManager,
            $this->contentAggregator,
            $this->contentResolver,
            $this->requestAnalyzer
        );
    }

    public function testGetFunctionsReturnsExpectedFunctions(): void
    {
        $functions = $this->extension->getFunctions();

        $this->assertCount(2, $functions);

        $functionNames = array_map(fn($fn) => $fn->getName(), $functions);
        $this->assertContains('sulu_get_archives', $functionNames);
        $this->assertContains('sulu_resolve_archive', $functionNames);
    }

    public function testResolveArchiveReturnsNullWhenNoLocalization(): void
    {
        $this->requestAnalyzer->method('getCurrentLocalization')
            ->willReturn(null);

        $result = $this->extension->resolveArchive('some-uuid');

        $this->assertNull($result);
    }

    public function testResolveArchiveReturnsNullWhenArchiveNotFound(): void
    {
        $localization = $this->createMock(Localization::class);
        $localization->method('getLocale')->willReturn('en');

        $this->requestAnalyzer->method('getCurrentLocalization')
            ->willReturn($localization);

        $this->archiveRepository->method('findByUuid')
            ->with('non-existent-uuid')
            ->willReturn(null);

        $result = $this->extension->resolveArchive('non-existent-uuid');

        $this->assertNull($result);
    }

    public function testResolveArchiveReturnsResolvedContent(): void
    {
        $localization = $this->createMock(Localization::class);
        $localization->method('getLocale')->willReturn('en');

        $this->requestAnalyzer->method('getCurrentLocalization')
            ->willReturn($localization);

        $archive = $this->createMock(Archive::class);
        $dimensionContent = $this->createMock(ArchiveDimensionContent::class);
        $dimensionContent->method('getTitle')->willReturn('Test Archive');

        $this->archiveRepository->method('findByUuid')
            ->with('test-uuid')
            ->willReturn($archive);

        $this->contentAggregator->method('aggregate')
            ->with(
                $archive,
                [
                    'locale' => 'en',
                    'stage' => DimensionContentInterface::STAGE_LIVE,
                    'version' => DimensionContentInterface::CURRENT_VERSION,
                ]
            )
            ->willReturn($dimensionContent);

        $expectedResult = ['title' => 'Test Archive'];
        $this->contentResolver->method('resolve')
            ->with($dimensionContent, [])
            ->willReturn($expectedResult);

        $result = $this->extension->resolveArchive('test-uuid');

        $this->assertEquals($expectedResult, $result);
    }

    public function testGetArchivesReturnsEmptyArrayWhenNoLocalization(): void
    {
        $this->requestAnalyzer->method('getCurrentLocalization')
            ->willReturn(null);

        $result = $this->extension->getArchives();

        $this->assertEquals([], $result);
    }
}
