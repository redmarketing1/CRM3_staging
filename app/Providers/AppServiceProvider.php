<?php

namespace App\Providers;

use App\View\MainLayoutComposer;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Fxcjahid\LaravelAssetsManager\Traits\AddsAsset;
use Fxcjahid\LaravelAssetsManager\Foundation\Asset\Manager\AssetManager;
use Fxcjahid\LaravelAssetsManager\Foundation\Asset\Pipeline\AssetPipeline;
use Fxcjahid\LaravelAssetsManager\Foundation\Asset\Manager\FleetCartAssetManager;
use Fxcjahid\LaravelAssetsManager\Foundation\Asset\Pipeline\FleetCartAssetPipeline;
use Fxcjahid\LaravelAssetsManager\Foundation\Asset\Types\AssetTypeFactory as AssetFactory;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register() : void
    {
        $this->app->singleton(AssetManager::class, FleetCartAssetManager::class);

        $this->app->singleton(AssetPipeline::class, function ($app) {
            return new FleetCartAssetPipeline($app[AssetManager::class]);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot() : void
    {
        View::composer('layouts.main', MainLayoutComposer::class);

        $this->addModulesAssets();
    }

    /**
     * Add modules assets to the asset manager.
     *
     * @return void
     */
    private function addModulesAssets()
    {
        foreach ($this->app['modules']->allEnabled() as $module) {
            $assets = config("fleetcart.modules.{$module->get('alias')}.assets");

            if (! is_null($assets)) {
                $this->addAssets($assets);
            }
        }
    }

    /**
     * Add the assets from the config file on the asset manager.
     *
     * @param array $allAssets
     * @return void
     */
    private function addAssets($assets)
    {
        // Add all assets to the AssetManager
        foreach ($assets['all_assets'] ?? [] as $assetName => $assetPath) {
            $url = $this->app[AssetFactory::class]->make($assetPath)->url();

            $this->app[AssetManager::class]->addAsset($assetName, $url);
        }
    }

}
