# Archiv-Einstellungen

Zugriff über Sulu Admin → Archiv → Einstellungen (falls implementiert)

## Konfiguration

Die Hauptkonfiguration erfolgt in `config/packages/sulu_archive.yaml`:

```yaml
sulu_archive:
    default_type: 'default'
    types:
        default:
            name: 'sulu_archive.types.default'
            color: '#6c757d'
        # ... weitere Typen
```

## Archiv-Eintrag Optionen

Jeder Archiv-Eintrag unterstützt folgende Optionen:

### Grundeinstellungen
- **Typ** - Auswahl aus konfigurierten Archiv-Typen
- **Autor anzeigen** - Autorname im Frontend anzeigen
- **Datum anzeigen** - Veröffentlichungsdatum im Frontend anzeigen

### Inhaltsfelder
- **Titel** - Haupttitel (erforderlich)
- **Untertitel** - Optionaler sekundärer Titel
- **Zusammenfassung** - Kurze Einleitung
- **Text** - Hauptinhalt mit Rich-Text-Editor
- **Fußzeile** - Fußnoten, Quellenangaben

### Medien
- **Hauptbild** - Hero-/Featured-Bild
- **Dokument** - PDF oder andere herunterladbare Datei
- **Galerie** - Mehrere Bilder (über Template-Blöcke)

### Autoreninformationen
- **Autor** - Sulu-Kontakt als Autor
- **Verfassungsdatum** - Veröffentlichungsdatum

## Template-Konfiguration

Templates sind gespeichert in `Resources/config/templates/archives/`:

```xml
<template xmlns="http://schemas.sulu.io/template/template"
          xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
          xsi:schemaLocation="http://schemas.sulu.io/template/template http://schemas.sulu.io/template/template.xsd">
    <key>archive</key>
    <view>archive</view>
    <controller>Manuxi\SuluArchiveBundle\Controller\Website\ArchiveController::indexAction</controller>
    <!-- Eigenschaften -->
</template>
```
