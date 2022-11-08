<?php

namespace ZanySoft\Zip;

use Illuminate\Support\ServiceProvider;

class ZipServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerZipService();
    }

    /**
     * Register currency provider.
     *
     * @return void
     */
    public function registerZipService()
    {
        $this->app->singleton('zanysoft.zip', function ($app) {
            return new Zip();
        });
    }
}