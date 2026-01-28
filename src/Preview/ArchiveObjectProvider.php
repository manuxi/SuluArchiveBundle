<?php

declare(strict_types=1);

namespace Manuxi\SuluArchiveBundle\Preview;

use Manuxi\SuluArchiveBundle\Entity\Archive;
use Manuxi\SuluArchiveBundle\Repository\ArchiveRepository;
use Sulu\Bundle\PreviewBundle\Preview\PreviewContext;
use Sulu\Bundle\PreviewBundle\Preview\Provider\PreviewDefaultsProviderInterface;
use Sulu\Content\Application\ContentAggregator\ContentAggregatorInterface;
use Sulu\Content\Domain\Model\DimensionContentInterface;

class ArchiveObjectProvider implements PreviewDefaultsProviderInterface
{
    public function __construct(
        private readonly ArchiveRepository $archiveRepository,
        private readonly ContentAggregatorInterface $contentAggregator,
    ) {
    }

    public function getDefaults(PreviewContext $previewContext): array
    {
        $archive = $this->archiveRepository->findByUuid($previewContext->getId());

        if (!$archive) {
            return [];
        }

        $dimensionContent = $this->contentAggregator->aggregate(
            $archive,
            [
                'locale' => $previewContext->getLocale(),
                'stage' => DimensionContentInterface::STAGE_DRAFT,
            ]
        );

        if (!$dimensionContent) {
            return [];
        }

        return [
            '_controller' => 'Manuxi\SuluArchiveBundle\Controller\Website\ArchiveController::indexAction',
            'archive' => $archive,
            'object' => $dimensionContent,
        ];
    }

    public function updateValues(PreviewContext $previewContext, array $defaults, array $data): array
    {
        $dimensionContent = $defaults['dimensionContent'] ?? null;

        if ($dimensionContent) {
            if (isset($data['title'])) {
                $dimensionContent->setTitle($data['title']);
            }
            if (isset($data['subtitle'])) {
                $dimensionContent->setSubtitle($data['subtitle']);
            }
            if (isset($data['summary'])) {
                $dimensionContent->setSummary($data['summary']);
            }
            if (isset($data['text'])) {
                $dimensionContent->setText($data['text']);
            }
            if (isset($data['footer'])) {
                $dimensionContent->setFooter($data['footer']);
            }
        }

        return $defaults;
    }

    public function updateContext(PreviewContext $previewContext, array $defaults, array $context): array
    {
        $dimensionContent = $defaults['dimensionContent'] ?? null;

        if ($dimensionContent && \array_key_exists('template', $context)) {
            $dimensionContent->setTemplateKey($context['template']);
        }

        return $defaults;
    }

    public function getSecurityContext(PreviewContext $previewContext): ?string
    {
        return Archive::SECURITY_CONTEXT;
    }
}
