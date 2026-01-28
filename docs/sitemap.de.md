# Sitemap-Integration

Das SuluArchiveBundle integriert sich automatisch mit Sulus Sitemap-Generierung.

## Automatische Integration

Alle veröffentlichten Archiv-Einträge werden automatisch in die Sitemap aufgenommen. Das Bundle stellt einen `ArchiveSitemapProvider` bereit, der:

- Alle veröffentlichten Archive pro Sprache auflistet
- Das letzte Änderungsdatum einschließt
- Webspace-Konfigurationen berücksichtigt

## Konfiguration

Keine zusätzliche Konfiguration erforderlich. Der Sitemap-Provider wird automatisch über den Service-Container registriert.

## Sitemap-URL

Archive erscheinen in der Sitemap unter ihren konfigurierten Routen:

```
https://deine-domain.de/de/archiv/dein-archiv-eintrag
https://deine-domain.de/en/archive/your-archive-entry
```

## Archive von der Sitemap ausschließen

Um bestimmte Archive von der Sitemap auszuschließen, kannst du:

1. Den Archiv-Eintrag depublizieren
2. Oder einen eigenen Sitemap-Provider implementieren, der nach deinen Kriterien filtert

## Technische Details

Der Sitemap-Provider ist mit dem Tag `sulu.sitemap.provider` registriert:

```yaml
sulu_archive.sitemap_provider:
    class: Manuxi\SuluArchiveBundle\Sitemap\ArchiveSitemapProvider
    tags:
        - { name: sulu.sitemap.provider }
```
