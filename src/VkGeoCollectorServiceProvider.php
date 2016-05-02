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
        //
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
