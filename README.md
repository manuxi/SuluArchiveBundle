# SuluArchiveBundle!
![php workflow](https://github.com/manuxi/SuluArchiveBundle/actions/workflows/php.yml/badge.svg)
![symfony workflow](https://github.com/manuxi/SuluArchiveBundle/actions/workflows/symfony.yml/badge.svg)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://github.com/manuxi/SuluArchiveBundle/LICENSE)
![GitHub Tag](https://img.shields.io/github/v/tag/manuxi/SuluArchiveBundle)
![Github Release](https://img.shields.io/github/v/release/manuxi/SuluArchiveBundle?color=116ea3)
![Supports Sulu 3.0 or later](https://img.shields.io/badge/%20Sulu->=3.0-0088cc?color=00b2df)

[🇩🇪 German Version](README.de.md)

The SuluArchiveBundle extends Sulu CMS with comprehensive archive management.
It enables the creation and management of archive entries with detailed information, documents, media galleries and multilingual support.
Over 30 customizable archive types allow flexible categorization of historical documents, photos, newspaper articles and more.

## ✨ Features

### 📚 Archive Management
- **Extensive Archive Details** - Title, subtitle, summary, text, footer/sources
- **Document Management** - PDF attachments for downloadable documents
- **Media Integration** - Main images and image galleries
- **30+ Archive Types** - Streets, buildings, historical documents, photos, newspaper articles, and more
- **SEO & Excerpt** - Full SEO and excerpt management
- **Multilingual** - Full translation support
- **Author Management** - Assign contacts as archive authors
- **More** - Trash, references, sitemaps, etc.

### 🔄 Advanced Features
- **Smart Content** - Usable as a content block in any Sulu page
- **Teaser Provider** - Archives available as teasers
- **Link Provider** - Easy linking to archives in text editors
- **Sitemap Integration** - Automatic sitemap generation
- **Search Integration** - Full-text search in admin and website

## 📋 Prerequisites

- PHP 8.2 or higher
- Sulu CMS 3.0 or higher
- Symfony 6.2 or higher
- MySQL 5.7+ / MariaDB 10.2+ / PostgreSQL 11+

## 👩🏻‍🏭 Installation

### Step 1: Install the package

```bash
composer require manuxi/sulu-archive-bundle
```

If you are *not* using Symfony Flex, add the bundle to `config/bundles.php`:

```php
return [
    //...
    Manuxi\SuluArchiveBundle\SuluArchiveBundle::class => ['all' => true],
];
```

### Step 2: Configure routes

Add to `routes_admin.yaml`:

```yaml
SuluArchiveBundle:
    resource: '@SuluArchiveBundle/Resources/config/routes_admin.yaml'
```

For website frontend, add to `routes_website.yaml`:

```yaml
SuluArchiveBundle:
    resource: '@SuluArchiveBundle/Resources/config/routes_website.yaml'
```

### Step 3: Update the database

```bash
# Check what will be created
php bin/console doctrine:schema:update --dump-sql

# Execute migration
php bin/console doctrine:schema:update --force
```

### Step 4: Grant permissions

1. Go to Sulu Admin → Settings → User Roles
2. Find the appropriate role
3. Enable permissions for "Archives"
4. Reload the page

## 🎣 Usage

### Create your first archive entry

1. Navigate to **Archive** in the Sulu admin navigation
2. Click on **Add archive**
3. Select an archive type
4. Fill in the details (title, text, images, documents)
5. Configure author settings (optional)
6. Publish your archive entry

## 🧶 Configuration

Configuration documentation: [Settings](docs/settings.en.md)

## 📖 Documentation

Detailed documentation in the [docs/](docs/) directory.

- [Archive Types](docs/archive-types.en.md) - Configure custom archive types
- [Sitemap](docs/sitemap.en.md) - Sitemap integration
- [Settings](docs/settings.en.md) - Configuration options

## 👩‍🍳 Contributing

Contributions are welcome! Please create issues or pull requests.

## 📝 License

This bundle is licensed under the MIT License. See [LICENSE](LICENSE).

## 🎉 Credits

Created and maintained by [manuxi](https://github.com/manuxi).

Thanks to the Sulu team for the great CMS and fantastic support!

And thank *you* for your support and testing!
