<?php

namespace Hzone\VkGeoCollector;

use Illuminate\Database\Eloquent\Model;

class VkGeoCollectorCity extends Model
{
	protected $table        = 'vk_geo_cities';
	protected $fillable     = [
		'city_id',
		'region_id',
		'country_id',
		'lang',
		'title',
		'area',
	];
	protected $primaryKey   = 'city_id';
	public    $timestamps   = false;
	public    $incrementing = false;
}
