<?php

namespace morningtrain\laravelcvr;

use Illuminate\Support\ServiceProvider;

class laravelcvrServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {

        $this->publishes([
            __DIR__.'/config/cvr.php' => config_path('cvr.php'),
        ]);

    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}