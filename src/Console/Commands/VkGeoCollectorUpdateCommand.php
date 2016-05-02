<?php namespace Hzone\VkGeoCollector\Console\Commands;

use Illuminate\Console\Command;
use Config;
use \Exception;
use Hzone\VkGeoCollector\VkGeoCollectorCountry as Country;
use Hzone\VkGeoCollector\VkGeoCollectorRegion as Region;
use Hzone\VkGeoCollector\VkGeoCollectorCity as City;

class VkGeoCollectorUpdateCommand extends Command
{
	protected $name        = 'vk-geo-collector:update';
	protected $description = 'Collects Countries/Regions/Cities from VKontakte social network, and update local database with collected data.';
	protected $signature   = 'vk-geo-collector:update
													{lang? : (required) language id from README.md }
													{limit? : (optional) limit/offset for response}';

	protected $collection         = [ ];
	protected $languages          = null;
	protected $lang               = null;
	protected $limit              = null;
	protected $countryBaseUrl     = null;
	protected $regionBaseUrl      = null;
	protected $cityBaseUrl        = null;
	protected $status             = [
		'countries' => [
			'collected' => 0,
			'updated'   => 0,
			'new'       => 0,
			'skipped'   => 0,
		],
		'regions'   => [
			'collected' => 0,
			'updated'   => 0,
			'new'       => 0,
			'skipped'   => 0,
		],
		'cities'    => [
			'collected' => 0,
			'updated'   => 0,
			'new'       => 0,
			'skipped'   => 0,
		],
		'time'      => [
			'start'   => 0,
			'end'     => 0,
			'minutes' => 0,
		],
	];
	protected $progressbarStarted = false;

	public function handle()
	{
		$this->status[ 'time' ][ 'start' ] = microtime( 1 );
		$this->languages                   = Config::get( 'vkgc.languages' );
		$this->countryBaseUrl              = Config::get( 'vkgc.country' );
		$this->regionBaseUrl               = Config::get( 'vkgc.region' );
		$this->cityBaseUrl                 = Config::get( 'vkgc.city' );
		try
		{
			$this->lang  = intval( $this->argument( 'lang' ) );
			$this->limit = intval( $this->argument( 'limit' ) );
			if ( !empty( $this->languages[ $this->lang ] ) )
			{
				$this->info( 	"Start at: " . date( "d.m.Y H:i:s\n" ) );
				$this->info( 	"Begin collecting Geo data for language " . $this->languages[ $this->lang ][ 'title' ] . "..." );
				$this->comment( "The average time execution of the script ~ 20 minutes.\n" );
				$this->countries();
				$this->regions();
				$this->cities();
			}
			$this->generateOutputStatus();
		}
		catch ( \Exception $e )
		{
			$this->error( $e );
		}
	}

	protected function countries()
	{
		$this->info( 'Collecting Countries...' );
		$this->_getCountries( $this->countryBaseUrl[ 'params' ][ 'offset' ] );
		$this->status[ 'countries' ][ 'collected' ] = count( $this->collection );
	}

	protected function _getCountries( $offset = 0 )
	{
		$url  = $this->buildCountryUrl( $this->lang, $offset );
		$temp = $this->makeRequest( $url );
		if ( !empty( $temp[ 'response' ][ 'count' ] ) )
		{
			$count = $temp[ 'response' ][ 'count' ];
			$items = $temp[ 'response' ][ 'items' ];
			if ( $this->progressbarStarted == false )
			{
				$this->output->progressStart( $count );
				$this->progressbarStarted = true;
			}
			for ( $x = 0; $x <= count( $items ) - 1; $x++ )
			{
				$this->collection[ $items[ $x ][ 'id' ] ] = [
					'title'   => $items[ $x ][ 'title' ],
					'regions' => [ ],
				];
				$this->updateCountryDB( $items[ $x ][ 'id' ], $this->collection[ $items[ $x ][ 'id' ] ] );
				if ( $this->progressbarStarted == true )
				{
					$this->output->progressAdvance();
				}
			}
			if ( $count > count( $this->collection ) )
			{
				$this->getCountries( count( $this->collection ) );
			}
		}
		if ( $this->progressbarStarted == true )
		{
			$this->output->progressFinish();
			$this->progressbarStarted = false;
		}
	}

	protected function regions()
	{
		$this->info( 'Collecting Regions...' );
		if ( !empty( $this->collection ) )
		{
			foreach ( $this->collection as $country_id => $country )
			{
				$this->_getRegions( $country_id, $this->regionBaseUrl[ 'params' ][ 'offset' ] );
			}
		}
		if ( $this->progressbarStarted == true )
		{
			$this->output->progressFinish();
			$this->progressbarStarted = false;
		}
	}

	protected function _getRegions( $country_id = 0, $offset = 0 )
	{
		$url  = $this->buildRegionUrl( $this->lang, $offset, $country_id );
		$temp = $this->makeRequest( $url );
		if ( !empty( $temp[ 'response' ][ 'count' ] ) )
		{
			$count = $temp[ 'response' ][ 'count' ];
			$items = $temp[ 'response' ][ 'items' ];
			$this->status[ 'regions' ][ 'collected' ] += count( $items );
			if ( $this->progressbarStarted == false )
			{
				$this->output->progressStart( $this->status[ 'regions' ][ 'collected' ] );
				$this->progressbarStarted = true;
			}
			for ( $x = 0; $x <= count( $items ) - 1; $x++ )
			{
				$this->collection[ $country_id ][ 'regions' ][ $items[ $x ][ 'id' ] ] = [
					'title'  => $items[ $x ][ 'title' ],
					'cities' => 0,
				];
				$this->updateRegionDB( $items[ $x ][ 'id' ], $country_id, $this->collection[ $country_id ][ 'regions' ][ $items[ $x ][ 'id' ] ] );
				if ( $this->progressbarStarted == true )
				{
					$this->output->progressAdvance();
				}
			}
			if ( $count > count( $this->collection[ $country_id ][ 'regions' ] ) )
			{
				$this->_getRegions( $country_id, count( $this->collection[ $country_id ][ 'regions' ] ) );
			}
		}
	}

	protected function cities()
	{
		$this->info( 'Collecting Cities...' );
		if ( !empty( $this->collection ) )
		{
			foreach ( $this->collection as $country_id => $country )
			{
				if ( !empty( $country[ 'regions' ] ) )
				{
					foreach ( $country[ 'regions' ] as $region_id => $region )
					{
						$this->_getCities( $region_id, $country_id, $this->cityBaseUrl[ 'params' ][ 'offset' ] );
					}
				}
				else
				{
					$this->_getCities( 0, $country_id, $this->cityBaseUrl[ 'params' ][ 'offset' ] );

				}
			}
		}
		if ( $this->progressbarStarted == true )
		{
			$this->output->progressFinish();
			$this->progressbarStarted = false;
		}
	}

	protected function _getCities( $region_id = 0, $country_id = 0, $offset = 0 )
	{
		$url  = $this->buildCityUrl( $this->lang, $offset, $region_id, $country_id );
		$temp = $this->makeRequest( $url );
		if ( !empty( $temp[ 'response' ][ 'count' ] ) )
		{
			if ( empty( $this->collection[ $country_id ][ 'regions' ][ $region_id ][ 'cities' ] ) )
			{
				$this->collection[ $country_id ][ 'regions' ][ $region_id ][ 'cities' ] = 0;
			}
			$count = $temp[ 'response' ][ 'count' ];
			$items = $temp[ 'response' ][ 'items' ];
			$this->status[ 'cities' ][ 'collected' ] += count( $items );
			if ( $this->progressbarStarted == false )
			{
				$this->output->progressStart( $this->status[ 'cities' ][ 'collected' ] );
				$this->progressbarStarted = true;
			}
			for ( $x = 0; $x <= count( $items ) - 1; $x++ )
			{
				$temp2 = [
					'title' => $items[ $x ][ 'title' ],
					'area'  => ( !empty( $items[ $x ][ 'area' ] ) )
						? $items[ $x ][ 'area' ]
						: null,
				];
				$this->collection[ $country_id ][ 'regions' ][ $region_id ][ 'cities' ]++;
				$this->updateCityDB( $items[ $x ][ 'id' ], $region_id, $country_id, $temp2 );
				if ( $this->progressbarStarted == true )
				{
					$this->output->progressAdvance();
				}
			}
			if ( $count > $this->collection[ $country_id ][ 'regions' ][ $region_id ][ 'cities' ] )
			{
				$this->_getCities( $region_id, $country_id, $this->collection[ $country_id ][ 'regions' ][ $region_id ][ 'cities' ] );
			}
		}
	}

	protected function buildCountryUrl( $lang, $offset )
	{
		$baseUrl                         = $this->countryBaseUrl;
		$baseUrl[ 'params' ][ 'lang' ]   = $lang;
		$baseUrl[ 'params' ][ 'offset' ] = $offset;
		return $baseUrl[ 'url' ] . http_build_query( $baseUrl[ 'params' ] );
	}

	protected function buildRegionUrl( $lang, $offset, $country_id = 0 )
	{
		$baseUrl                             = $this->regionBaseUrl;
		$baseUrl[ 'params' ][ 'lang' ]       = $lang;
		$baseUrl[ 'params' ][ 'offset' ]     = $offset;
		$baseUrl[ 'params' ][ 'country_id' ] = $country_id;
		return $baseUrl[ 'url' ] . http_build_query( $baseUrl[ 'params' ] );
	}

	protected function buildCityUrl( $lang, $offset, $region_id = 0, $country_id = 0 )
	{
		$baseUrl                             = $this->cityBaseUrl;
		$baseUrl[ 'params' ][ 'lang' ]       = $lang;
		$baseUrl[ 'params' ][ 'offset' ]     = $offset;
		$baseUrl[ 'params' ][ 'country_id' ] = $country_id;
		if ( empty( $region_id ) )
		{
			unset ( $baseUrl[ 'params' ][ 'region_id' ] );
		}
		else
		{
			$baseUrl[ 'params' ][ 'region_id' ] = $region_id;
		}
		return $baseUrl[ 'url' ] . http_build_query( $baseUrl[ 'params' ] );
	}

	protected function makeRequest( $url )
	{
		try
		{
			$array = [ ];
			$ch    = curl_init();
			curl_setopt( $ch, CURLOPT_URL, $url );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
			curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );    # required for https urls
			curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 1200 );
			curl_setopt( $ch, CURLOPT_TIMEOUT, 1200 );
			$content  = curl_exec( $ch );
			//$response = curl_getinfo( $ch );
			curl_close( $ch );
			if ( !empty( $content ) )
			{
				$array = json_decode( $content, true );
			}
			return $array;
		}
		catch ( \Exception $ex )
		{
			dd( $ex );
		}
		/**
		 * // variant with file_get_contents - may fall to error due to timeout error
		 * $array = [ ];
		 * $json  = file_get_contents( $url, false, stream_context_create( [
		 * 		'http' => [
		 *			'method'  => "GET",
		 *			'timeout' => 1200,
		 *		],
		 *	] ) );
		 *	if ( !empty( $json ) )
		 *	{
		 * 		$array = json_decode( $json, true );
		 *	}
		 *	return $array;
		 */
	}

	protected function updateCollectionDB()
	{
		$this->info( 'Updating Collection...' );
		if ( $this->progressbarStarted == false )
		{
			$this->output->progressStart( $this->status[ 'countries' ][ 'collected' ] + $this->status[ 'regions' ][ 'collected' ] + $this->status[ 'cities' ][ 'collected' ] );
			$this->progressbarStarted = true;
		}
		if ( !empty( $this->collection ) )
		{
			foreach ( $this->collection as $country_id => $country )
			{
				$this->updateCountryDB( $country_id, $country );
				if ( $this->progressbarStarted == true )
				{
					$this->output->progressAdvance( 1 );
				}
				if ( !empty( $country[ 'regions' ] ) )
				{
					foreach ( $country[ 'regions' ] as $region_id => $region )
					{
						$this->updateRegionDB( $region_id, $country_id, $region );
						if ( $this->progressbarStarted == true )
						{
							$this->output->progressAdvance( 1 );
						}
						if ( !empty( $region[ 'cities' ] ) )
						{
							foreach ( $region[ 'cities' ] as $city_id => $city )
							{
								$this->updateCityDB( $city_id, $region_id, $country_id, $city );
								if ( $this->progressbarStarted == true )
								{
									$this->output->progressAdvance( 1 );
								}
							}
						}
					}
				}
			}
		}
		if ( $this->progressbarStarted == true )
		{
			$this->output->progressFinish();
			$this->progressbarStarted = false;
		}
	}

	protected function updateCountryDB( $country_id, $country )
	{
		$Country = Country::where( 'country_id', '=', $country_id )
						  ->where( 'lang', '=', $this->languages[ $this->lang ][ 'code' ] )
						  ->first()
		;
		if ( !empty( $Country ) )
		{
			if ( $Country->title !== $country[ 'title' ] )
			{
				$Country->title = $country[ 'title' ];
				$Country->save();
				$this->status[ 'countries' ][ 'updated' ]++;
			}
			else
			{
				$this->status[ 'countries' ][ 'skipped' ]++;
			}
		}
		else
		{
			Country::create( [
				'country_id' => $country_id,
				'lang'       => $this->languages[ $this->lang ][ 'code' ],
				'title'      => $country[ 'title' ],
			] );
			$this->status[ 'countries' ][ 'new' ]++;
		}
	}

	protected function updateRegionDB( $region_id, $country_id, $region )
	{
		if ( empty( $region_id ) )
		{
			return;
		}
		$Region = Region::where( 'country_id', '=', $country_id )
						->where( 'lang', '=', $this->languages[ $this->lang ][ 'code' ] )
						->first()
		;
		if ( !empty( $Region ) )
		{
			if ( $Region->title !== $region[ 'title' ] )
			{
				$Region->title = $region[ 'title' ];
				$Region->save();
				$this->status[ 'regions' ][ 'updated' ]++;
			}
			else
			{
				$this->status[ 'regions' ][ 'skipped' ]++;
			}
		}
		else
		{
			Region::create( [
				'region_id'  => $region_id,
				'country_id' => $country_id,
				'lang'       => $this->languages[ $this->lang ][ 'code' ],
				'title'      => $region[ 'title' ],
			] );
			$this->status[ 'regions' ][ 'new' ]++;
		}
	}

	protected function updateCityDB( $city_id, $region_id, $country_id, $city )
	{
		$temp = City::where( 'city_id', '=', $city_id )
					->where( 'country_id', '=', $country_id )
					->where( 'lang', '=', $this->languages[ $this->lang ][ 'code' ] )
		;
		if ( empty( $region_id ) )
		{
			$region_id = null;
			$temp->whereNull( 'region_id' );
		}
		else
		{
			$temp->where( 'region_id', '=', $region_id );
		}
		$City = $temp->first();
		if ( !empty( $City ) )
		{
			if ( $City->title !== $city[ 'title' ] )
			{
				$City->title = $city[ 'title' ];
				$City->save();
				$this->status[ 'regions' ][ 'updated' ]++;
			}
			elseif ( $City->area !== $city[ 'area' ] )
			{
				$City->area = $city[ 'area' ];
				$City->save();
				$this->status[ 'regions' ][ 'updated' ]++;
			}
			else
			{
				$this->status[ 'regions' ][ 'skipped' ]++;
			}
		}
		else
		{
			City::create( [
				'city_id'    => $city_id,
				'region_id'  => $region_id,
				'country_id' => $country_id,
				'lang'       => $this->languages[ $this->lang ][ 'code' ],
				'title'      => $city[ 'title' ],
				'area'       => ( !empty( $city[ 'area' ] ) )
					? $city[ 'area' ]
					: null,
			] );
			$this->status[ 'regions' ][ 'new' ]++;
		}
	}

	protected function generateOutputStatus()
	{
		$this->info( "\nStatus:" );
		$this->table( [
			'',
			'collected',
			'updated',
			'new',
			'skipped',
		], [
			[
				'country',
				$this->status[ 'countries' ][ 'collected' ],
				$this->status[ 'countries' ][ 'updated' ],
				$this->status[ 'countries' ][ 'new' ],
				$this->status[ 'countries' ][ 'skipped' ],
			],
			[
				'region',
				$this->status[ 'regions' ][ 'collected' ],
				$this->status[ 'regions' ][ 'updated' ],
				$this->status[ 'regions' ][ 'new' ],
				$this->status[ 'regions' ][ 'skipped' ],
			],
			[
				'city',
				$this->status[ 'cities' ][ 'collected' ],
				$this->status[ 'cities' ][ 'updated' ],
				$this->status[ 'cities' ][ 'new' ],
				$this->status[ 'cities' ][ 'skipped' ],
			],
		] );
		$this->info( "\nMemory Usage       : " . round( memory_get_usage() / 1000, 3 ) );
		$this->info( 'Memory Usage (real): ' . round( memory_get_usage( true ) / 1000, 3 ) );
		$this->info( 'Memory Usage (peak)      : ' . round( memory_get_peak_usage() / 1000, 3 ) );
		$this->info( 'Memory Usage (peak; real): ' . round( memory_get_peak_usage( true ) / 1000, 3 ) );
		$this->info( 'End at:   ' . date( 'd.m.Y H:i:s' ) );
	}
}
