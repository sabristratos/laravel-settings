<?php

namespace Stratos\Settings\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Stratos\Settings\Console\Commands\SettingsClearCacheCommand;
use Stratos\Settings\Console\Commands\SettingsCreateCommand;
use Stratos\Settings\Console\Commands\SettingsExportCommand;
use Stratos\Settings\Console\Commands\SettingsGetCommand;
use Stratos\Settings\Console\Commands\SettingsImportCommand;
use Stratos\Settings\Console\Commands\SettingsListCommand;
use Stratos\Settings\Console\Commands\SettingsSetCommand;
use Stratos\Settings\Contracts\SettingsManagerContract;
use Stratos\Settings\Contracts\UserSettingsManagerContract;
use Stratos\Settings\Http\Middleware\ShareSettingsMiddleware;
use Stratos\Settings\Managers\SettingsManager;
use Stratos\Settings\Managers\UserSettingsManager;
use Stratos\Settings\Models\Setting;
use Stratos\Settings\Models\UserSetting;
use Stratos\Settings\Observers\SettingObserver;
use Stratos\Settings\Observers\UserSettingObserver;

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
