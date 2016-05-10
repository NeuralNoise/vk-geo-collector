# vk-geo-collector

Collect GEO data from social networf VKontakte: Countries, Cities.
WARNING! Regions is not supported anymore!

## Installation

### composer

```bash
composer require h-zone/vk-geo-collector:~0.2
```

or

`composer.json`
```json
"h-zone/vk-geo-collector": "~0.2"
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
```
php artisan vendor:publish --provider="Hzone\VkGeoCollector\VkGeoCollectorServiceProvider" --tag="config"
php artisan vendor:publish --provider="Hzone\VkGeoCollector\VkGeoCollectorServiceProvider" --tag="migrations"
php artisan migrate
```

## Usage
```sh
php artisan vk-geo-collector:update 0
```
This command will query VK for Worldwide Countries / Cities on desired languande (zero as param), then insert these data into database.
If local pair id-title is identical, this pair will be skipped (useful for update-checks the local database).

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

* https://vk.com/dev/database
* https://vk.com/dev/database.getCountries
* https://vk.com/dev/database.getCities
