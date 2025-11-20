# Migration Guides

Step-by-step guides for migrating from other solutions to Laravel Settings.

## Table of Contents

- [From Spatie Laravel Settings](#from-spatie-laravel-settings)
- [From Config Files](#from-config-files)
- [From Custom Implementation](#from-custom-implementation)

## From Spatie Laravel Settings

Migrating from `spatie/laravel-settings` to Laravel Settings.

### Differences

| Feature | Spatie | Laravel Settings |
|---------|--------|-----------------|
| User settings | ❌ | ✅ |
| Encryption | ❌ | ✅ |
| Validation | ❌ | ✅ |
| Permissions | ❌ | ✅ |
| Audit trail | ❌ | ✅ |
| Import/Export | ❌ | ✅ |
| Groups | ✅ | ✅ |
| Caching | ✅ | ✅ |

### Migration Steps

#### 1. Install Laravel Settings

```bash
composer require stratos/laravel-settings
php artisan vendor:publish --tag="settings-migrations"
php artisan migrate
```

#### 2. Export Existing Settings

```php
// Create migration script
use Spatie\LaravelSettings\Settings as SpatieSettings;
use Stratos\Settings\Facades\Settings;

// If using settings classes
$oldSettings = app(GeneralSettings::class);

Settings::setBulk([
    'site.name' => $oldSettings->site_name,
    'site.email' => $oldSettings->contact_email,
]);

// If using direct access
$allSettings = SpatieSettings::all();

foreach ($allSettings as $key => $value) {
    Settings::set($key, $value);
}
```

#### 3. Update Code

**Before (Spatie):**

```php
use Spatie\LaravelSettings\Settings;

class GeneralSettings extends Settings
{
    public string $site_name;

    public static function group(): string
    {
        return 'general';
    }
}

// Usage
$settings = app(GeneralSettings::class);
echo $settings->site_name;
```

**After (Laravel Settings):**

```php
use Stratos\Settings\Facades\Settings;

// Usage
echo Settings::get('site.name');
echo setting('site.name');
```

#### 4. Remove Spatie Package

```bash
composer remove spatie/laravel-settings
```

## From Config Files

Migrating from config files to dynamic settings.

### When to Migrate

Migrate to Laravel Settings if you need:
- Settings to change without deployments
- User-specific preferences
- UI for managing settings
- Audit trail of changes

### Migration Steps

#### 1. Identify Settings to Migrate

```php
// config/site.php (old)
return [
    'name' => env('SITE_NAME', 'My Application'),
    'email' => env('SITE_EMAIL', 'info@example.com'),
    'per_page' => env('PER_PAGE', 15),
];
```

#### 2. Create Settings in Database

```php
use Stratos\Settings\Facades\Settings;

Settings::setBulk([
    'site.name' => config('site.name'),
    'site.email' => config('site.email'),
    'site.per_page' => config('site.per_page'),
]);
```

#### 3. Update Code References

**Before:**

```php
$siteName = config('site.name');
$email = config('site.email');
```

**After:**

```php
$siteName = Settings::get('site.name', 'Default Name');
$email = Settings::get('site.email', 'default@example.com');

// Or using helper
$siteName = setting('site.name', 'Default Name');
```

#### 4. Create Seeder

```php
use Illuminate\Database\Seeder;
use Stratos\Settings\Facades\Settings;

class SettingsSeeder extends Seeder
{
    public function run()
    {
        Settings::setBulk([
            'site.name' => env('SITE_NAME', 'My Application'),
            'site.email' => env('SITE_EMAIL', 'info@example.com'),
            'site.per_page' => env('PER_PAGE', 15),
        ]);
    }
}
```

## From Custom Implementation

Migrating from a custom settings table or implementation.

### Common Custom Schema

```sql
CREATE TABLE settings (
    id INT PRIMARY KEY,
    key VARCHAR(255) UNIQUE,
    value TEXT
);
```

### Migration Steps

#### 1. Export Existing Data

```php
// Create one-time migration command
use Illuminate\Support\Facades\DB;
use Stratos\Settings\Facades\Settings;

$customSettings = DB::table('old_settings')->get();

foreach ($customSettings as $setting) {
    Settings::set($setting->key, $setting->value);
}
```

#### 2. Update Model References

**Before:**

```php
use App\Models\Setting;

$siteName = Setting::where('key', 'site.name')->value('value');
```

**After:**

```php
use Stratos\Settings\Facades\Settings;

$siteName = Settings::get('site.name');
```

#### 3. Migrate Helper Functions

**Before:**

```php
function setting($key, $default = null) {
    return \App\Models\Setting::where('key', $key)->value('value') ?? $default;
}
```

**After:**

```php
// Use built-in helper
$value = setting('key', 'default');
```

#### 4. Drop Old Table

```php
// After verifying migration
Schema::dropIfExists('old_settings');
```

---

[← Testing](testing.md) | [Troubleshooting →](troubleshooting.md)
