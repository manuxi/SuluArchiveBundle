# SuluArchiveBundle!
![php workflow](https://github.com/manuxi/SuluArchiveBundle/actions/workflows/php.yml/badge.svg)
![symfony workflow](https://github.com/manuxi/SuluArchiveBundle/actions/workflows/symfony.yml/badge.svg)
<a href="https://github.com/manuxi/SuluArchiveBundle/blob/main/LICENSE" target="_blank">
<img src="https://img.shields.io/github/license/manuxi/SuluArchiveBundle" alt="GitHub license">
</a>
<a href="https://github.com/manuxi/SuluArchiveBundle/tags" target="_blank">
<img src="https://img.shields.io/github/v/tag/manuxi/SuluArchiveBundle" alt="GitHub license">
</a>

I made this bundle to have the possibility to manage archive data in my projects. 

This bundle contains
- Several filters for Archive Content Type
- Link Provider
- Sitemap Provider
- Handler for Trash Items
- Handler for Automation
- Possibility to assign a contact as author
- Twig Extension for resolving archive elements / get a list of archive elements
- Events for displaying Activities
and more...

The archive elements and their meta information is translatable. 

It contains an example twig template. 

Please feel comfortable submitting feature requests. 
This bundle is still in development. Use at own risk 🤞🏻

## 👩🏻‍🏭 Installation
Install the package with:
```console
composer require manuxi/sulu-archive-bundle
```
If you're *not* using Symfony Flex, you'll also
need to add the bundle in your `config/bundles.php` file:

```php
return [
    //...
    Manuxi\SuluArchiveBundle\SuluArchiveBundle::class => ['all' => true],
];
```
Please add the following to your `routes_admin.yaml`:
```yaml
SuluArchiveBundle:
    resource: '@SuluArchiveBundle/Resources/config/routes_admin.yml'
```
Last but not least the schema of the database needs to be updated.  

Some tables will be created (prefixed with app_):  
archive, archive_translation, archive_seo, archive_excerpt
(plus some ManyToMany relation tables).  

See the needed queries with
```
php bin/console doctrine:schema:update --dump-sql
```  
Update the schema by executing 
```
php bin/console doctrine:schema:update --force
```  

Make sure you only process the bundles schema updates!

## 🎣 Usage
First: Grant permissions for Archive. 
After page reload you should see the archive item in the navigation. 
Start to create archive elements.
Use smart_content property type to show a list of archive elements, e.g.:
```xml
<property name="archivelist" type="smart_content">
    <meta>
        <title lang="en">Archive</title>
        <title lang="de">Archiv</title>
    </meta>
    <params>
        <param name="provider" value="Archive"/>
        <param name="max_per_page" value="5"/>
        <param name="page_parameter" value="page"/>
    </params>
</property>
```
Example of the corresponding twig template for the Archive list:
```html
{% for archive in archivelist %}
    <div class="col">
        <h2>
            {{ archive.title }}
        </h2>
        <h3>
            {{ archive.subtitle }}
        </h3>
        <p>
            {{ archive.created|format_datetime('full', 'none', locale=app.request.getLocale()) }}
        </p>
        <p>
            {{ archive.summary|raw }}
        </p>
        <p>
            <a class="btn btn-primary" href="{{ archive.routePath }}" role="button">
                {{ "Read more..."|trans }} <i class="fa fa-angle-double-right"></i>
            </a>
        </p>
    </div>
{% endfor %}
```

Since the seo and excerpt tabs are available in the archive editor, 
meta information can be provided like it's done as usual when rendering your pages. 

## 👩‍🍳 Contributing
For the sake of simplicity this extension was kept small.
Please feel comfortable submitting issues or pull requests. As always I'd be glad to get your feedback to improve the extension :).
