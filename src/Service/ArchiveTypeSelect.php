<?php

declare(strict_types=1);

namespace Manuxi\SuluArchiveBundle\Service;

use Sulu\Bundle\MediaBundle\Content\Types\CollectionSelection;
use Symfony\Contracts\Translation\TranslatorInterface;

class ArchiveTypeSelect
{

    private TranslatorInterface $translator;
    private array $typesMap = [
        'default'                   => 'sulu_archive.types.default',
        'streets'                   => 'sulu_archive.types.streets',
        'traffic'                   => 'sulu_archive.types.traffic',
        'trains'                    => 'sulu_archive.types.trains',
        'signs'                     => 'sulu_archive.types.signs',
        'attractions'               => 'sulu_archive.types.attractions',
        'memorials'                 => 'sulu_archive.types.memorials',
        'buildings'                 => 'sulu_archive.types.buildings',
        'mining'                    => 'sulu_archive.types.mining',
        'surrounding_area'          => 'sulu_archive.types.surrounding_area',

        'maps_plans'                => 'sulu_archive.types.maps_plans',
        'aerial_shots'              => 'sulu_archive.types.aerial_shots',
        'development_plans'         => 'sulu_archive.types.development_plans',
        'expert_opinions_reports'   => 'sulu_archive.types.expert_opinions_reports',

        'place_name_studies'        => 'sulu_archive.types.place_name_studies',
        'local_chronicles'          => 'sulu_archive.types.local_chronicles',
        'newspaper_articles'        => 'sulu_archive.types.newspaper_articles',
        'advertisements'            => 'sulu_archive.types.advertisements',
        'posters_flyers'            => 'sulu_archive.types.posters_flyers',
        'objects_artifacts'         => 'sulu_archive.types.objects_artifacts',
        'collections_exhibitions'   => 'sulu_archive.types.collections_exhibitions',

        'genealogical_research'     => 'sulu_archive.types.genealogical_research',
        'biographies'               => 'sulu_archive.types.biographies',
        'correspondences'           => 'sulu_archive.types.correspondences',

        'historical_documents'      => 'sulu_archive.types.historical_documents',
        'historical_recordings'     => 'sulu_archive.types.historical_recordings',
        'visual_material'           => 'sulu_archive.types.visual_material',
        'audio_video_recordings'    => 'sulu_archive.types.audio_video_recordings',

        'membership_directories'    => 'sulu_archive.types.membership_directories',
        'club_journals'             => 'sulu_archive.types.club_journals',

    ];
    private string $defaultValue = 'default';

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function getValues(): array
    {
        $values = [];

        foreach ($this->typesMap as $code => $toTrans) {
            $values[] = [
                'name' => $code,
                'title' => $this->translator->trans($toTrans, [], 'admin'),
            ];
        }

        return $values;
    }

    public function getDefaultValue(): string
    {
        return $this->defaultValue;
    }
}