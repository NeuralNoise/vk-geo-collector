<?php

namespace Hzone\VkGeoCollector;

use Illuminate\Database\Eloquent\Model;

class VkGeoCollectorCountry extends Model
{
	protected $table        = 'vk_geo_countries';
	protected $fillable     = [
		'country_id',
		'lang',
		'title',
	];
	protected $primaryKey   = 'country_id';
	public    $timestamps   = false;
	public    $incrementing = false;
}
