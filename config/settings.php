<?php

/*
|--------------------------------------------------------------------------
| Laravel Settings Configuration
|--------------------------------------------------------------------------
|
| This file contains all configuration options for the Laravel Settings
| package. Settings can be site-wide or user-specific, with support for
| caching, encryption, validation, multilingual content, and more.
|
*/

return [

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Configure caching behavior for settings. Caching significantly improves
    | performance by reducing database queries. Settings are automatically
    | invalidated when updated, deleted, or when flush() is called.
    |
    */

    'cache' => [
        // Enable or disable caching for settings
        'enabled' => env('SETTINGS_CACHE_ENABLED', true),

        // Cache driver to use (null uses default cache driver)
        'driver' => env('SETTINGS_CACHE_DRIVER', config('cache.default')),

        // Cache key prefix to avoid conflicts with other cached data
        'prefix' => env('SETTINGS_CACHE_PREFIX', 'settings'),

        // Cache TTL in seconds (3600 = 1 hour)
        'ttl' => env('SETTINGS_CACHE_TTL', 3600),
    ],

    /*
    |--------------------------------------------------------------------------
    | Database Table Names
    |--------------------------------------------------------------------------
    |
    | Specify custom table names for settings storage. Change these if you
    | need to avoid conflicts with existing tables in your database.
    |
    */

    'tables' => [
        'settings' => 'settings',
        'user_settings' => 'user_settings',
        'setting_histories' => 'setting_histories',
    ],

    /*
    |--------------------------------------------------------------------------
    | Encryption Configuration
    |--------------------------------------------------------------------------
    |
    | Settings can be encrypted for sensitive data like API keys, passwords,
    | and tokens. Uses Laravel's encryption (APP_KEY). Enable globally here,
    | or specify per-setting using the encrypted flag.
    |
    */

    'encryption' => [
        // Enable encryption support (can still be toggled per-setting)
        'enabled' => env('SETTINGS_ENCRYPTION_ENABLED', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Setting Values
    |--------------------------------------------------------------------------
    |
    | Define default values for settings that will be returned when a setting
    | doesn't exist in the database. Useful for application defaults.
    |
    | Example:
    | 'app.timezone' => 'UTC',
    | 'app.locale' => 'en',
    |
    */

    'defaults' => [
        // Add your default settings here
    ],

    /*
    |--------------------------------------------------------------------------
    | Setting Groups
    |--------------------------------------------------------------------------
    |
    | Organize settings into logical groups for better management. These are
    | used in admin interfaces and CLI commands to categorize settings.
    | Format: 'key' => 'Display Name'
    |
    */

    'groups' => [
        'site' => 'Site Settings',
        'system' => 'System Settings',
        'email' => 'Email Settings',
        'social' => 'Social Media',
        'seo' => 'SEO Settings',
    ],

    /*
    |--------------------------------------------------------------------------
    | Localization
    |--------------------------------------------------------------------------
    |
    | Configure supported locales for multilingual settings. Labels and
    | descriptions can be defined in multiple languages and will be
    | automatically resolved based on the application locale.
    |
    */

    // Available locales for multilingual settings
    'locales' => ['en', 'fr', 'de'],

    // Default locale used as fallback when translation is missing
    'default_locale' => 'en',

    /*
    |--------------------------------------------------------------------------
    | Metadata Definitions
    |--------------------------------------------------------------------------
    |
    | Pre-define metadata for specific settings. This is useful for settings
    | that need labels, validation rules, or input types defined ahead of time.
    | These definitions are optional and can be managed dynamically as well.
    |
    */

    'metadata_definitions' => [
        'site.name' => [
            'label' => ['en' => 'Site Name', 'de' => 'Seitenname'],
            'description' => ['en' => 'The name of your website', 'de' => 'Der Name Ihrer Website'],
            'validation_rules' => ['required', 'string', 'max:255'],
            'input_type' => 'text',
            'is_public' => true,
            'order' => 1,
        ],
        'site.email' => [
            'label' => ['en' => 'Contact Email', 'de' => 'Kontakt E-Mail'],
            'description' => ['en' => 'Primary contact email address', 'de' => 'Primäre Kontakt-E-Mail-Adresse'],
            'validation_rules' => ['required', 'email'],
            'input_type' => 'email',
            'is_public' => true,
            'order' => 2,
        ],
        'site.logo' => [
            'label' => ['en' => 'Logo URL', 'de' => 'Logo URL'],
            'description' => ['en' => 'URL to your site logo', 'de' => 'URL zu Ihrem Site-Logo'],
            'validation_rules' => ['url'],
            'input_type' => 'url',
            'is_public' => true,
            'order' => 3,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Audit Trail Configuration
    |--------------------------------------------------------------------------
    |
    | Track all setting changes including who made the change, when, and from
    | where. History is stored in the setting_histories table and includes
    | old/new values for rollback capability.
    |
    */

    'audit' => [
        // Enable change tracking for all settings
        'enabled' => env('SETTINGS_AUDIT_ENABLED', true),

        // Store IP address with each change
        'track_ip' => env('SETTINGS_AUDIT_TRACK_IP', true),

        // Store user agent with each change
        'track_user_agent' => env('SETTINGS_AUDIT_TRACK_USER_AGENT', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | View Sharing Configuration
    |--------------------------------------------------------------------------
    |
    | Automatically share settings with all views using the ShareSettingsMiddleware.
    | Settings will be available in views as $settings['key.name'].
    | Apply middleware: Route::middleware('share-settings')
    |
    */

    'share' => [
        // Enable automatic view sharing
        'enabled' => env('SETTINGS_SHARE_ENABLED', false),

        // Specific setting keys to share (empty = share all)
        'keys' => [],

        // Specific groups to share (empty = share all)
        'groups' => [],

        // Only share public settings (recommended for security)
        'public_only' => env('SETTINGS_SHARE_PUBLIC_ONLY', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | REST API Configuration
    |--------------------------------------------------------------------------
    |
    | Enable REST API endpoints for settings management. Useful for SPAs,
    | mobile apps, or external integrations. Routes are automatically
    | registered when enabled.
    |
    */

    'api' => [
        // Enable REST API endpoints
        'enabled' => env('SETTINGS_API_ENABLED', false),

        // API route prefix
        'prefix' => env('SETTINGS_API_PREFIX', 'api/settings'),

        // Middleware applied to API routes
        'middleware' => ['api', 'auth:sanctum'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Import/Export Configuration
    |--------------------------------------------------------------------------
    |
    | Configure default behavior for import/export operations via CLI or API.
    | Supports JSON and YAML formats for easy backup and migration.
    |
    */

    'import_export' => [
        // Allowed file formats for import/export
        'allowed_formats' => ['json', 'yaml'],

        // Include metadata (labels, validation, etc.) in exports
        'include_metadata' => true,

        // Include encrypted settings in exports (not recommended)
        'include_encrypted' => false,
    ],

];
