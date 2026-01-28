# Sitemap Integration

The SuluArchiveBundle automatically integrates with Sulu's sitemap generation.

## Automatic Integration

All published archive entries are automatically included in the sitemap. The bundle provides a `ArchiveSitemapProvider` that:

- Lists all published archives per locale
- Includes the last modification date
- Respects webspace configurations

## Configuration

No additional configuration is required. The sitemap provider is automatically registered via the service container.

## Sitemap URL

Archives appear in the sitemap under their configured routes:

```
https://your-domain.com/en/archive/your-archive-entry
https://your-domain.com/de/archiv/dein-archiv-eintrag
```

## Excluding Archives from Sitemap

To exclude specific archives from the sitemap, you can:

1. Unpublish the archive entry
2. Or implement a custom sitemap provider that filters based on your criteria

## Technical Details

The sitemap provider is registered with the tag `sulu.sitemap.provider`:

```yaml
sulu_archive.sitemap_provider:
    class: Manuxi\SuluArchiveBundle\Sitemap\ArchiveSitemapProvider
    tags:
        - { name: sulu.sitemap.provider }
```
