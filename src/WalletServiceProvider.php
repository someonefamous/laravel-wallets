<?php

namespace SomeoneFamous\Wallets;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class WalletServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'sf_wallets');
    }

    public function boot()
    {
        if ($this->app->runningInConsole()) {
            if (!class_exists('SetUpWalletTables')) {
                $this->publishes([
                    __DIR__ . '/../database/migrations/set_up_wallet_tables.php.stub' => database_path(
                        'migrations/' . date('Y_m_d_His') . '_set_up_wallet_tables.php'
                    ),
                ], 'migrations');
            }

            $this->publishes([
                __DIR__.'/../config/config.php' => config_path('sf_wallets.php'),
            ], 'config');

            $this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/sf_wallets'),
            ], 'views');
        }

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'sf_wallets');

        $this->registerRoutes();
    }

    protected function registerRoutes()
    {
        Route::group($this->routeConfiguration(), function () {
            $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        });
    }

    protected function routeConfiguration()
    {
        return [
            'prefix' => config('sf_wallets.prefix'),
            'middleware' => config('sf_wallets.middleware'),
        ];
    }
}
