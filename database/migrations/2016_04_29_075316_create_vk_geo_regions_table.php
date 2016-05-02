<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVkGeoRegionsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create( 'vk_geo_regions', function ( Blueprint $table )
		{
			$table->bigInteger( 'region_id' )
				  ->index()
			;
			$table->bigInteger( 'country_id' )
				  ->index()
			;
			$table->string( 'lang', 2 )
				  ->index()
			;
			$table->string( 'title' )
				  ->index()
			;
		} );
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop( 'vk_geo_regions' );
	}
}
