<?php

namespace Strata\Settings\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Strata\Settings\Console\Commands\SettingsClearCacheCommand;
use Strata\Settings\Console\Commands\SettingsCreateCommand;
use Strata\Settings\Console\Commands\SettingsExportCommand;
use Strata\Settings\Console\Commands\SettingsGetCommand;
use Strata\Settings\Console\Commands\SettingsImportCommand;
use Strata\Settings\Console\Commands\SettingsListCommand;
use Strata\Settings\Console\Commands\SettingsSetCommand;
use Strata\Settings\Contracts\SettingsManagerContract;
use Strata\Settings\Contracts\UserSettingsManagerContract;
use Strata\Settings\Http\Middleware\ShareSettingsMiddleware;
use Strata\Settings\Managers\SettingsManager;
use Strata\Settings\Managers\UserSettingsManager;
use Strata\Settings\Models\Setting;
use Strata\Settings\Models\UserSetting;
use Strata\Settings\Observers\SettingObserver;
use Strata\Settings\Observers\UserSettingObserver;

class SettingsServiceProvider extends ServiceProvider
{
    /**
     * Register any package services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../../config/settings.php',
            'settings'
        );

        $this->app->singleton('settings', function ($app) {
            return new SettingsManager;
        });

        $this->app->alias('settings', SettingsManager::class);

        $this->app->bind(SettingsManagerContract::class, SettingsManager::class);
        $this->app->bind(UserSettingsManagerContract::class, UserSettingsManager::class);

        $this->app->singleton(ShareSettingsMiddleware::class);
    }

    /**
     * Bootstrap any package services.
     */
    public function boot(): void
    {
        $this->registerObservers();
        $this->registerPublishables();
        $this->registerCommands();
        $this->registerRoutes();
        $this->registerBladeDirectives();
        $this->registerMiddleware();
    }

    /**
     * Register model observers.
     */
    protected function registerObservers(): void
    {
        Setting::observe(SettingObserver::class);
        UserSetting::observe(UserSettingObserver::class);
    }

    /**
     * Register publishable assets.
     */
    protected function registerPublishables(): void
    {
        if ($this->app->runningInConsole()) {
            $timestamp = date('Y_m_d_His');

            $this->publishes([
                __DIR__.'/../../database/migrations/create_settings_table.php' => database_path("migrations/{$timestamp}_create_settings_table.php"),
            ], 'settings-migrations');

            $this->publishes([
                __DIR__.'/../../database/migrations/create_user_settings_table.php' => database_path("migrations/{$timestamp}_create_user_settings_table.php"),
            ], 'settings-migrations');

            $this->publishes([
                __DIR__.'/../../database/migrations/create_setting_histories_table.php' => database_path("migrations/{$timestamp}_create_setting_histories_table.php"),
            ], 'settings-migrations');

            $this->publishes([
                __DIR__.'/../../config/settings.php' => config_path('settings.php'),
            ], 'settings-config');
        }
    }

    /**
     * Register console commands.
     */
    protected function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                SettingsGetCommand::class,
                SettingsSetCommand::class,
                SettingsCreateCommand::class,
                SettingsListCommand::class,
                SettingsExportCommand::class,
                SettingsImportCommand::class,
                SettingsClearCacheCommand::class,
            ]);
        }
    }

    /**
     * Register package routes.
     */
    protected function registerRoutes(): void
    {
        if (config('settings.api.enabled', false)) {
            Route::group([], function () {
                $this->loadRoutesFrom(__DIR__.'/../../routes/api.php');
            });
        }
    }

    /**
     * Register custom Blade directives.
     */
    protected function registerBladeDirectives(): void
    {
        Blade::directive('setting', function ($expression) {
            return "<?php echo e(setting({$expression})); ?>";
        });

        Blade::directive('userSetting', function ($expression) {
            return "<?php echo e(user_setting({$expression})); ?>";
        });
    }

    /**
     * Register middleware.
     */
    protected function registerMiddleware(): void
    {
        $this->app['router']->aliasMiddleware('share-settings', ShareSettingsMiddleware::class);
    }
}
