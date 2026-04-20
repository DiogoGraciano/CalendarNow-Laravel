<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
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
        Blade::directive('themeAssets', function () {
            return "<?php echo app('Illuminate\\Foundation\\Vite')([\App\Support\ThemeResolver::cssAsset(), \App\Support\ThemeResolver::jsAsset()]); ?>";
        });
    }
}
