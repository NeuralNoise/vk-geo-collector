<?php

namespace Hzone\VkGeoCollector;

use Illuminate\Database\Eloquent\Model;

class VkGeoCollectorRegion extends Model
{
	protected $table        = 'vk_geo_regions';
	protected $fillable     = [
		'region_id',
		'country_id',
		'lang',
		'title',
	];
	protected $primaryKey   = 'region_id';
	public    $timestamps   = false;
	public    $incrementing = false;
}
