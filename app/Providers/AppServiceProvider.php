<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL; // ← 追加
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 本番環境（production）のときはhttpsを強制する
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
