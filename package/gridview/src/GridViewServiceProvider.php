<?php

namespace DawoodShahid\GridView;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;

class GridViewServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind('DawoodShahid\GridView\GridView', function ($app) {
            return new GridView();
        });
    }

    public function boot()
    {
        $this->loadViewsFrom(__DIR__ . '/resources/views', 'grid_view');
    }
}