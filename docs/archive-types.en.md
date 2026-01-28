# Archive Types

The SuluArchiveBundle comes with over 30 pre-configured archive types for flexible categorization.

## Default Types

### Places & Infrastructure
| Type | Key | Color |
|------|-----|-------|
| Default | `default` | #6c757d |
| Streets and Places | `streets` | #0d6efd |
| Traffic | `traffic` | #fd7e14 |
| Trains and Tracks | `trains` | #dc3545 |
| Signs and Boards | `signs` | #20c997 |
| Attractions | `attractions` | #ffc107 |
| Memorials | `memorials` | #6610f2 |
| Buildings and Architecture | `buildings` | #17a2b8 |
| Mining | `mining` | #795548 |
| Surrounding Area | `surrounding_area` | #28a745 |

### Maps & Plans
| Type | Key | Color |
|------|-----|-------|
| Maps and Plans | `maps_plans` | #2196f3 |
| Aerial Shots | `aerial_shots` | #03a9f4 |
| Development Plans | `development_plans` | #009688 |
| Expert Opinions and Reports | `expert_opinions_reports` | #607d8b |

### Written Materials
| Type | Key | Color |
|------|-----|-------|
| Place Name Studies | `place_name_studies` | #9c27b0 |
| Local Chronicles | `local_chronicles` | #673ab7 |
| Newspaper Articles | `newspaper_articles` | #3f51b5 |
| Advertisements | `advertisements` | #e91e63 |

### People & Families
| Type | Key | Color |
|------|-----|-------|
| Genealogical Research | `genealogical_research` | #8bc34a |
| Biographies | `biographies` | #4caf50 |
| Correspondences | `correspondences` | #cddc39 |

### Historical Media
| Type | Key | Color |
|------|-----|-------|
| Historical Documents | `historical_documents` | #ff9800 |
| Historical Recordings | `historical_recordings` | #ff5722 |
| Photos and Visual Material | `visual_material` | #9e9e9e |
| Audio and Video Recordings | `audio_video_recordings` | #f44336 |

### Organization Materials
| Type | Key | Color |
|------|-----|-------|
| Membership Directories | `membership_directories` | #00bcd4 |
| Club Journals | `club_journals` | #3d5afe |
| Posters and Flyers | `posters_flyers` | #d500f9 |
| Objects and Artifacts | `objects_artifacts` | #ffab00 |
| Collections and Exhibitions | `collections_exhibitions` | #00e676 |

## Custom Configuration

You can override or extend types in your project's configuration:

```yaml
# config/packages/sulu_archive.yaml
sulu_archive:
    default_type: 'default'
    types:
        custom_type:
            name: 'sulu_archive.types.custom_type'
            color: '#ff0000'
```

Add translations for your custom types:

```yaml
# translations/admin.en.yaml
sulu_archive:
    types:
        custom_type: "My Custom Type"
```

## Type Display

Types are displayed in:
- Archive list view (colored badge)
- Archive edit form (dropdown selection)
- Website frontend (if included in template)
