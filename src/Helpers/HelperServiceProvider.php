<?php

namespace rjmangini\Helpers;

use Illuminate\Support\ServiceProvider;

class HelperServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes( [ __DIR__ . '/config.php' => config_path( 'helpers.php' ) ] );
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerHelper();
        $this->app->alias( 'helper', Helper::class );
    }

    protected function registerHelper()
    {
        $this->app->singleton(
            'helper',
            function () {
                return new Helper();
            }
        );
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [ 'helper' ];
    }
}
