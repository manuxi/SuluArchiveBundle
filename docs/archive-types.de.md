# Archiv-Typen

Das SuluArchiveBundle kommt mit über 30 vorkonfigurierten Archiv-Typen für flexible Kategorisierung.

## Standard-Typen

### Orte & Infrastruktur
| Typ | Schlüssel | Farbe |
|-----|-----------|-------|
| Standard | `default` | #6c757d |
| Straßen und Plätze | `streets` | #0d6efd |
| Verkehr | `traffic` | #fd7e14 |
| Züge und Gleise | `trains` | #dc3545 |
| Schilder und Tafeln | `signs` | #20c997 |
| Sehenswürdigkeiten | `attractions` | #ffc107 |
| Denkmäler | `memorials` | #6610f2 |
| Gebäude und Architektur | `buildings` | #17a2b8 |
| Bergbau | `mining` | #795548 |
| Umgebung | `surrounding_area` | #28a745 |

### Karten & Pläne
| Typ | Schlüssel | Farbe |
|-----|-----------|-------|
| Landkarten und Pläne | `maps_plans` | #2196f3 |
| Luftaufnahmen | `aerial_shots` | #03a9f4 |
| Bebauungspläne | `development_plans` | #009688 |
| Gutachten und Berichte | `expert_opinions_reports` | #607d8b |

### Schriftliche Materialien
| Typ | Schlüssel | Farbe |
|-----|-----------|-------|
| Ortsnamenkunde | `place_name_studies` | #9c27b0 |
| Ortschroniken | `local_chronicles` | #673ab7 |
| Zeitungsartikel | `newspaper_articles` | #3f51b5 |
| Werbeanzeigen | `advertisements` | #e91e63 |

### Personen & Familien
| Typ | Schlüssel | Farbe |
|-----|-----------|-------|
| Familienforschung | `genealogical_research` | #8bc34a |
| Biographien | `biographies` | #4caf50 |
| Korrespondenzen | `correspondences` | #cddc39 |

### Historische Medien
| Typ | Schlüssel | Farbe |
|-----|-----------|-------|
| Historische Dokumente | `historical_documents` | #ff9800 |
| Historische Aufnahmen | `historical_recordings` | #ff5722 |
| Fotos und Bildmaterial | `visual_material` | #9e9e9e |
| Ton- und Videoaufnahmen | `audio_video_recordings` | #f44336 |

### Vereinsmaterialien
| Typ | Schlüssel | Farbe |
|-----|-----------|-------|
| Mitgliederverzeichnisse | `membership_directories` | #00bcd4 |
| Vereinszeitschriften | `club_journals` | #3d5afe |
| Plakate und Flyer | `posters_flyers` | #d500f9 |
| Objekte und Artefakte | `objects_artifacts` | #ffab00 |
| Sammlungen und Ausstellungen | `collections_exhibitions` | #00e676 |

## Eigene Konfiguration

Du kannst Typen in deiner Projektkonfiguration überschreiben oder erweitern:

```yaml
# config/packages/sulu_archive.yaml
sulu_archive:
    default_type: 'default'
    types:
        custom_type:
            name: 'sulu_archive.types.custom_type'
            color: '#ff0000'
```

Füge Übersetzungen für deine eigenen Typen hinzu:

```yaml
# translations/admin.de.yaml
sulu_archive:
    types:
        custom_type: "Mein eigener Typ"
```

## Typ-Anzeige

Typen werden angezeigt in:
- Archiv-Listenansicht (farbiges Badge)
- Archiv-Bearbeitungsformular (Dropdown-Auswahl)
- Website-Frontend (falls im Template eingebunden)
