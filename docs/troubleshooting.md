# Troubleshooting

Common issues and solutions when using Laravel Settings.

## Table of Contents

- [Installation Issues](#installation-issues)
- [Cache Issues](#cache-issues)
- [Permission Issues](#permission-issues)
- [Encryption Issues](#encryption-issues)
- [Performance Issues](#performance-issues)
- [Migration Issues](#migration-issues)

## Installation Issues

### Tables Already Exist

**Problem**: Migration fails with "table already exists" error

**Solution**:
```bash
# Drop existing tables
php artisan db:wipe

# Re-run migrations
php artisan migrate

# Or use fresh migration
php artisan migrate:fresh
```

### Package Not Found

**Problem**: `Class 'Stratos\Settings\SettingsServiceProvider' not found`

**Solution**:
```bash
# Clear config cache
php artisan config:clear

# Dump autoload
composer dump-autoload

# Reinstall package
composer require stratos/laravel-settings
```

## Cache Issues

### Settings Don't Update

**Problem**: Changed settings don't reflect immediately

**Solution**:
```bash
# Clear settings cache
php artisan settings:clear-cache

# Or clear all cache
php artisan cache:clear

# Verify cache is enabled in config
php artisan tinker
>>> config('settings.cache.enabled')
```

### Cache Driver Issues

**Problem**: "Cache store [redis] is not defined"

**Solution**:
```env
# In .env, use a configured cache driver
SETTINGS_CACHE_DRIVER=file

# Or configure Redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

## Permission Issues

### Permission Checks Always Fail

**Problem**: `canView()` or `canEdit()` always returns false

**Solution**:

1. **Check Spatie Permission is installed**:
```bash
composer require spatie/laravel-permission
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
php artisan migrate
```

2. **Verify config**:
```php
// config/settings.php
'permissions' => [
    'use_spatie_permission' => true,
],
```

3. **Check user has roles**:
```php
$user->roles; // Should return roles collection
$user->assignRole('admin'); // Assign role if missing
```

### Admin Bypass Not Working

**Problem**: Admin users can't access restricted settings

**Solution**:
```php
// Verify admin role matches config
// config/settings.php
'permissions' => [
    'admin_role' => 'admin', // Must match user's role
],

// Check user has admin role
$user->hasRole('admin'); // Should return true
```

## Encryption Issues

### Cannot Decrypt Values

**Problem**: "The payload is invalid" or decryption errors

**Solution**:

1. **Check APP_KEY is set**:
```env
# In .env
APP_KEY=base64:...

# Generate if missing
php artisan key:generate
```

2. **Don't change APP_KEY** after encrypting data - this will break decryption

3. **Re-encrypt if APP_KEY changed**:
```php
// You'll need to re-encrypt all encrypted settings
$encrypted = Setting::where('encrypted', true)->get();

foreach ($encrypted as $setting) {
    // Get old value (will fail)
    // Set new value with current APP_KEY
    Settings::setEncrypted($setting->key, $newValue);
}
```

### Encryption Not Working

**Problem**: Values stored as plain text despite using `setEncrypted()`

**Solution**:
```php
// Check encryption is enabled in config
// config/settings.php
'encryption' => [
    'enabled' => true,
],

// Verify in .env
SETTINGS_ENCRYPTION_ENABLED=true
```

## Performance Issues

### Slow Setting Retrieval

**Problem**: Getting settings is slow

**Solution**:

1. **Enable caching**:
```php
// config/settings.php
'cache' => [
    'enabled' => true,
    'ttl' => 3600,
],
```

2. **Use bulk operations**:
```php
// Slow - multiple queries
$name = Settings::get('site.name');
$email = Settings::get('site.email');
$logo = Settings::get('site.logo');

// Fast - single query
$settings = Settings::getBulk(['site.name', 'site.email', 'site.logo']);
```

3. **Optimize database**:
```sql
-- Ensure indexes exist
SHOW INDEXES FROM settings;

-- Should have index on 'key' column
```

### Large History Table

**Problem**: `setting_histories` table is huge and slowing down queries

**Solution**:

1. **Archive old history**:
```php
use Stratos\Settings\Models\SettingHistory;

// Archive history older than 1 year
$oldHistory = SettingHistory::where('created_at', '<', now()->subYear())->get();
Storage::put('archives/history.json', $oldHistory->toJson());

// Delete archived records
SettingHistory::where('created_at', '<', now()->subYear())->delete();
```

2. **Disable history tracking if not needed**:
```php
// config/settings.php
'audit' => [
    'enabled' => false,
],
```

## Migration Issues

### Foreign Key Constraint Fails

**Problem**: Cannot create user_settings table due to missing users table

**Solution**:
```bash
# Ensure users table exists first
php artisan migrate --path=database/migrations/2014_10_12_000000_create_users_table.php

# Then run settings migrations
php artisan migrate --path=vendor/stratos/laravel-settings/database/migrations
```

### Column Already Exists

**Problem**: "Column already exists" when running migrations

**Solution**:
```bash
# Check migration status
php artisan migrate:status

# Rollback if partially migrated
php artisan migrate:rollback

# Re-run migrations
php artisan migrate
```

## FAQ

### How do I reset all settings?

```php
use Stratos\Settings\Models\Setting;

Setting::truncate();
php artisan settings:clear-cache
```

### How do I backup settings?

```bash
php artisan settings:export backup-$(date +%Y%m%d).json --with-metadata
```

### Can I use settings in config files?

Not recommended. Config files are cached and settings are dynamic. Instead:

```php
// In service provider boot()
public function boot()
{
    config([
        'mail.from.address' => Settings::get('email.from.address', config('mail.from.address')),
    ]);
}
```

### How do I clear specific user's cache?

```bash
php artisan settings:clear-cache --user=1
```

Or programmatically:
```php
Settings::user($user)->flush();
```

### Settings not showing in Blade?

Check middleware is registered and settings are shared:

```php
// config/settings.php
'middleware' => [
    'share_to_views' => true,
    'share_keys' => ['site.name', 'site.logo'],
],

// Or manually share in controller
view()->share('siteName', setting('site.name'));
```

---

[← Migration Guides](migration-guides.md) | [Back to Index](index.md)
