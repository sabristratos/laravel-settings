# Installation

Get Laravel Settings up and running in your application in just a few minutes.

## Table of Contents

- [Requirements](#requirements)
- [Installation](#installation-steps)
- [Configuration](#configuration)
- [Quickstart](#quickstart)
- [Verification](#verification)
- [Next Steps](#next-steps)

## Requirements

Before installing Laravel Settings, ensure your environment meets these requirements:

- **PHP**: 8.3 or higher
- **Laravel**: 12.0 or higher
- **Database**: MySQL 5.7+, PostgreSQL 9.6+, SQLite 3.8+, or SQL Server 2017+

## Installation Steps

### Step 1: Install via Composer

```bash
composer require stratos/laravel-settings
```

### Step 2: Publish and Run Migrations

Publish the migration files and run them to create the necessary database tables:

```bash
php artisan vendor:publish --tag="settings-migrations"
php artisan migrate
```

This creates three tables:
- `settings` - Global application settings
- `user_settings` - Per-user preferences
- `setting_histories` - Audit trail for all changes

### Step 3: Publish Configuration (Optional)

If you want to customize the package configuration, publish the config file:

```bash
php artisan vendor:publish --tag="settings-config"
```

This creates `config/settings.php` where you can customize caching, encryption, table names, and more.

## Configuration

The package works out of the box with sensible defaults, but you can customize it by editing `config/settings.php`.

### Cache Configuration

```php
'cache' => [
    // Enable/disable caching
    'enabled' => env('SETTINGS_CACHE_ENABLED', true),

    // Cache driver (uses default if not specified)
    'driver' => env('SETTINGS_CACHE_DRIVER', config('cache.default')),

    // Cache key prefix
    'prefix' => env('SETTINGS_CACHE_PREFIX', 'settings'),

    // Time to live in seconds (1 hour default)
    'ttl' => env('SETTINGS_CACHE_TTL', 3600),
],
```

### Encryption Configuration

```php
'encryption' => [
    // Enable/disable encryption support
    'enabled' => env('SETTINGS_ENCRYPTION_ENABLED', true),
],
```

### Table Names

Customize the table names if needed:

```php
'tables' => [
    'settings' => 'settings',
    'user_settings' => 'user_settings',
    'setting_histories' => 'setting_histories',
],
```

### Audit Configuration

Configure history tracking and audit trail:

```php
'audit' => [
    // Enable automatic history tracking
    'enabled' => env('SETTINGS_AUDIT_ENABLED', true),

    // Track IP addresses
    'track_ip' => env('SETTINGS_AUDIT_TRACK_IP', true),

    // Track user agent strings
    'track_user_agent' => env('SETTINGS_AUDIT_TRACK_USER_AGENT', true),
],
```

### Default Settings

Define settings that should be automatically created when the package is installed:

```php
'defaults' => [
    'site.name' => 'My Application',
    'site.description' => 'A Laravel application',
    'app.maintenance_mode' => false,
    'app.per_page' => 15,
],
```

### Setting Groups

Define human-readable names for setting groups:

```php
'groups' => [
    'site' => 'Site Settings',
    'system' => 'System Settings',
    'email' => 'Email Settings',
    'social' => 'Social Media',
    'seo' => 'SEO Settings',
    'api' => 'API Configuration',
],
```

### Middleware Configuration

Configure which settings are shared with views:

```php
'middleware' => [
    // Automatically share settings with all views
    'share_to_views' => env('SETTINGS_SHARE_TO_VIEWS', false),

    // Specific setting keys to share
    'share_keys' => [
        'site.name',
        'site.logo',
        'site.description',
    ],

    // Or share all settings from specific groups
    'share_groups' => [
        'site',
    ],
],
```

### Permission Configuration

Configure permission system integration:

```php
'permissions' => [
    // Integration with Spatie Laravel Permission
    'use_spatie_permission' => env('SETTINGS_USE_SPATIE_PERMISSION', true),

    // Automatically detect admin users
    'admin_role' => env('SETTINGS_ADMIN_ROLE', 'admin'),
],
```

### REST API Configuration

Enable or disable the REST API:

```php
'api' => [
    // Enable REST API endpoints
    'enabled' => env('SETTINGS_API_ENABLED', false),

    // API route prefix
    'prefix' => env('SETTINGS_API_PREFIX', 'api/settings'),

    // API middleware
    'middleware' => ['api', 'auth:sanctum'],
],
```

## Quickstart

Let's create your first setting and retrieve it. This takes less than 1 minute!

### Using the Facade

```php
use Stratos\Settings\Facades\Settings;

// Create a setting
Settings::set('site.name', 'My Awesome Application');

// Retrieve a setting
$siteName = Settings::get('site.name');

// With a default value
$description = Settings::get('site.description', 'A great Laravel app');

// Check if a setting exists
if (Settings::has('site.name')) {
    // Setting exists
}

// Delete a setting
Settings::forget('site.name');
```

### Using the Helper

```php
// Get a setting (returns SettingsManager if no key provided)
$siteName = setting('site.name');

// With default
$siteName = setting('site.name', 'Default Name');

// Set a setting
setting()->set('site.name', 'My Application');
```

### Using Blade Directives

```blade
<!-- Display a setting value -->
<h1>@setting('site.name')</h1>

<!-- With fallback -->
<p>@setting('site.description') {{ 'Default description' }} @endsetting</p>

<!-- In attributes -->
<img src="@setting('site.logo')" alt="Logo">
```

### Using Artisan Commands

```bash
# Create a setting interactively
php artisan settings:create

# Set a setting
php artisan settings:set site.name "My Application"

# Get a setting
php artisan settings:get site.name

# List all settings
php artisan settings:list

# List settings in a group
php artisan settings:list --group=site
```

## Verification

Verify your installation is working correctly:

### 1. Check Tables Exist

```bash
php artisan tinker
```

```php
// Check if tables exist
DB::table('settings')->count(); // Should return 0 or number of default settings
DB::table('user_settings')->count(); // Should return 0
DB::table('setting_histories')->count(); // Should return 0
```

### 2. Create and Retrieve a Test Setting

```bash
php artisan tinker
```

```php
use Stratos\Settings\Facades\Settings;

// Create
Settings::set('test.verification', 'success');

// Retrieve
Settings::get('test.verification'); // Should return "success"

// Clean up
Settings::forget('test.verification');
```

### 3. Run Package Tests (if developing)

```bash
cd packages/strata/laravel-settings
vendor/bin/pest
```

All 131 tests should pass.

## Environment Variables

Add these to your `.env` file to customize behavior:

```env
# Caching
SETTINGS_CACHE_ENABLED=true
SETTINGS_CACHE_DRIVER=redis
SETTINGS_CACHE_PREFIX=settings
SETTINGS_CACHE_TTL=3600

# Encryption
SETTINGS_ENCRYPTION_ENABLED=true

# Audit Trail
SETTINGS_AUDIT_ENABLED=true
SETTINGS_AUDIT_TRACK_IP=true
SETTINGS_AUDIT_TRACK_USER_AGENT=true

# Permissions
SETTINGS_USE_SPATIE_PERMISSION=true
SETTINGS_ADMIN_ROLE=admin

# API
SETTINGS_API_ENABLED=false
SETTINGS_API_PREFIX=api/settings

# Middleware
SETTINGS_SHARE_TO_VIEWS=false
```

## Troubleshooting Installation

### Migration Errors

**Problem**: Migration fails with "table already exists"

**Solution**: Check if tables exist and drop them:

```bash
php artisan db:wipe
php artisan migrate
```

### Cache Issues

**Problem**: Settings don't update immediately

**Solution**: Clear the settings cache:

```bash
php artisan settings:clear-cache
# or
php artisan cache:clear
```

### Permission Errors

**Problem**: "Class 'Spatie\Permission\Models\Role' not found"

**Solution**: Install Spatie Permission or disable it in config:

```bash
composer require spatie/laravel-permission
# or set in config/settings.php
'permissions' => ['use_spatie_permission' => false]
```

## Next Steps

Now that Laravel Settings is installed, explore these guides:

1. **[Basic Usage](usage.md)** - Learn fundamental operations
2. **[Advanced Features](advanced-features.md)** - Type casting, validation, encryption
3. **[Recipes](recipes.md)** - See practical, real-world examples
4. **[API Reference](api-reference.md)** - Complete method documentation

---

**Need help?** Check the [Troubleshooting Guide](troubleshooting.md) or [open an issue](https://github.com/sabristratos/laravel-settings/issues).
