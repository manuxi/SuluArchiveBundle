# Archive Settings

Access via Sulu Admin → Archive → Settings (if implemented)

## Configuration

The main configuration is done in `config/packages/sulu_archive.yaml`:

```yaml
sulu_archive:
    default_type: 'default'
    types:
        default:
            name: 'sulu_archive.types.default'
            color: '#6c757d'
        # ... more types
```

## Archive Entry Options

Each archive entry supports the following options:

### Basic Settings
- **Type** - Select from configured archive types
- **Show Author** - Display author name on frontend
- **Show Date** - Display publication date on frontend

### Content Fields
- **Title** - Main title (required)
- **Subtitle** - Optional secondary title
- **Summary** - Brief introduction text
- **Text** - Main content with rich text editor
- **Footer** - Footnotes, source citations

### Media
- **Main Image** - Hero/featured image
- **Document** - PDF or other downloadable file
- **Gallery** - Multiple images (via template blocks)

### Author Information
- **Author** - Sulu contact as author
- **Authored Date** - Publication date

## Template Configuration

Templates are stored in `Resources/config/templates/archives/`:

```xml
<template xmlns="http://schemas.sulu.io/template/template"
          xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
          xsi:schemaLocation="http://schemas.sulu.io/template/template http://schemas.sulu.io/template/template.xsd">
    <key>archive</key>
    <view>archive</view>
    <controller>Manuxi\SuluArchiveBundle\Controller\Website\ArchiveController::indexAction</controller>
    <!-- properties -->
</template>
```
