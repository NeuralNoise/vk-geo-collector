<?php

namespace Hzone\VkGeoCollector;

use Illuminate\Support\ServiceProvider;
use Hzone\VkGeoCollector\Console\Commands\VkGeoCollectorUpdateCommand;

class VkGeoCollectorServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
		$this->publishes([
			__DIR__.'/../config/vkgc.php' => config_path('vkgc.php'),
		], 'config');
		$this->publishes([
			__DIR__.'/../database/migrations/' => database_path('/migrations')
		], 'migrations');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('command.vk-geo-collector.update', function()
        {
            return new VkGeoCollectorUpdateCommand;
        });
        $this->commands(
            'command.vk-geo-collector.update'
        );
    }
}
