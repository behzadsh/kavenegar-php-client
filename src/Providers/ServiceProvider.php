<?php
namespace Quince\kavenegar\Providers;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Quince\kavenegar\ClientBuilder;

class ServiceProvider extends BaseServiceProvider
{

    /**
     * Boot the service provider.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([realpath(__DIR__.'/../../config/kavenegar.php') => config_path('kavenegar.php')]);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bindShared('Quince\kavenegar\Client', function ($app) {
            $apiKey = $app['config']->get("kavenegar.api_key");
            $sender = $app['config']->get("kavenegar.sender");

            return ClientBuilder::build($apiKey, $sender);
        });
    }

}