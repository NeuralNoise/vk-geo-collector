# vk-update-geo

Collect GEO data from social networf VKontakte: Countries, Regions, Cities, etc

## Installation

### composer

```bash
composer require h-zone/vk-geo-collector:~0.1-dev
```

or

`composer.json`
```json
"h-zone/vk-geo-collector": "~0.1-dev"
```

### Lumen

`bootstrap/app.php`
```php
$app->register(Hzone\VkGeoCollector\VkGeoCollectorServiceProvider::class);
```

### Laravel 5+

`config/app.php`
```php
'providers' => [
    //....
    Hzone\VkGeoCollector\VkGeoCollectorServiceProvider::class,
    //....
],
```

### Both Lumen and Laravel
(!!! manually !!!)
Take the migrations from /vendor/h-zone/vk-geo-collector/database/migrations and apply it.
Take the configuration from /vendor/h-zone/vk-geo-collector/config

## Usage
```sh
php artisan vk-geo-collector:update --lang=0
```
This command will query VK for Worldwide Countries / Regions / Cities, and insert into database.
If local pair id-title is identical, this pair will be skipped (useful for updating the local database).

### Languages:

	id    => 0
	code  => ru
	title => Русский

	id    => 1
	code  => ua
	title => Українська мова

	id    => 3
	code  => en
	title => English

	id    => 4
	code  => es
	title => Español

	id    => 6
	code  => de
	title => Deutsch

	id    => 7
	code  => it
	title => Italiano

	id    => 12
	code  => pt
	title => Portoghese

	id    => 16
	code  => fr
	title => Français

### Ref.Docs:

https://vk.com/dev/database
https://vk.com/dev/database.getCountries
https://vk.com/dev/database.getRegions
https://vk.com/dev/database.getCities

