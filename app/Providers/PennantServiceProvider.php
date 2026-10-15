<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Pennant\Feature;

class PennantServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //Pennantが参照するスコープを「常にnull（グローバル）へ固定
        //<https:laravel.com/docs/13.x/pennant#default-scope>
        Feature::resolveScopeUsing(fn($driver) => null);

        //デフォルトは常にOFF。`Feature::activate()`などで値を保存すると
        //以降は保存済みの値が優先される(Pennantがfeaturesテーブルを参照)
        Feature::define('image.upload', fn() => false);
    }
}
