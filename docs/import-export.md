# Import & Export

Backup, migrate, and bulk manage your settings using the import/export features.

## Table of Contents

- [Exporting Settings](#exporting-settings)
- [Importing Settings](#importing-settings)
- [Bulk Operations](#bulk-operations)
- [Use Cases](#use-cases)

## Exporting Settings

### Export to JSON

```php
use Stratos\Settings\Facades\Settings;

// Export all settings
$json = Settings::export('json');

// Save to file
file_put_contents('settings-backup.json', $json);

// Export specific group
$json = Settings::export('json', ['group' => 'site']);
```

### Export to YAML

```php
// Export all settings as YAML
$yaml = Settings::export('yaml');

file_put_contents('settings-backup.yaml', $yaml);
```

### Export with Options

```php
// Include metadata (labels, descriptions, validation)
$json = Settings::export('json', [
    'include_metadata' => true,
]);

// Exclude encrypted values (for security)
$json = Settings::export('json', [
    'exclude_encrypted' => true,
]);

// Export specific group with metadata
$json = Settings::export('json', [
    'group' => 'api',
    'include_metadata' => true,
    'exclude_encrypted' => true,
]);
```

### Export via Artisan

```bash
# Export all settings to JSON
php artisan settings:export settings.json

# Export to YAML
php artisan settings:export settings.yaml --format=yaml

# Export specific group
php artisan settings:export site-settings.json --group=site

# Include metadata
php artisan settings:export full-backup.json --with-metadata

# Exclude encrypted values
php artisan settings:export safe-export.json --exclude-encrypted
```

## Importing Settings

### Import from JSON

```php
// Read file
$json = file_get_contents('settings-backup.json');

// Import
Settings::import($json, 'json');
```

### Import from YAML

```php
$yaml = file_get_contents('settings-backup.yaml');

Settings::import($yaml, 'yaml');
```

### Import with Options

```php
// Overwrite existing settings
Settings::import($json, 'json', [
    'overwrite' => true,
]);

// Skip existing settings (only add new ones)
Settings::import($json, 'json', [
    'overwrite' => false,
]);

// Dry run (validate without importing)
$result = Settings::import($json, 'json', [
    'dry_run' => true,
]);

// Import only specific group
Settings::import($json, 'json', [
    'group' => 'site',
]);
```

### Import via Artisan

```bash
# Import from JSON file
php artisan settings:import settings.json

# Import from YAML
php artisan settings:import settings.yaml --format=yaml

# Overwrite existing
php artisan settings:import settings.json --overwrite

# Dry run (test import)
php artisan settings:import settings.json --dry-run
```

## Bulk Operations

### Set Multiple Settings

```php
// Set many settings at once
Settings::setBulk([
    'site.name' => 'My Application',
    'site.description' => 'A great app',
    'site.email' => 'contact@example.com',
    'app.per_page' => 15,
    'feature.dark_mode' => true,
]);
```

### Get Multiple Settings

```php
// Get specific settings
$settings = Settings::getBulk([
    'site.name',
    'site.description',
    'site.email'
]);

// With defaults
$settings = Settings::getBulk([
    'site.name' => 'Default Name',
    'site.description' => 'Default Description',
    'site.email' => 'default@example.com',
]);
```

### Delete Multiple Settings

```php
Settings::forgetBulk([
    'old.setting1',
    'old.setting2',
    'deprecated.feature',
]);
```

### Bulk with Metadata

```php
Settings::setWithMetadataBulk([
    [
        'key' => 'site.name',
        'value' => 'My App',
        'label' => ['en' => 'Site Name'],
        'validationRules' => ['required', 'string', 'max:255'],
    ],
    [
        'key' => 'site.email',
        'value' => 'contact@example.com',
        'label' => ['en' => 'Contact Email'],
        'validationRules' => ['required', 'email'],
    ],
]);
```

## Use Cases

### Environment-Specific Configuration

```php
// Export from production
$prodSettings = Settings::export('json', ['group' => 'production']);

// Import to staging
Settings::import($prodSettings, 'json', ['overwrite' => false]);
```

### Backup Before Changes

```php
// Create backup before major changes
$backup = Settings::export('json');
file_put_contents(storage_path('backups/settings-' . date('Y-m-d') . '.json'), $backup);

// Make changes
Settings::set('api.provider', 'new-provider');

// If something goes wrong, restore
if ($error) {
    $backup = file_get_contents(storage_path('backups/settings-' . date('Y-m-d') . '.json'));
    Settings::import($backup, 'json', ['overwrite' => true]);
}
```

### Migration Between Applications

```php
// Export from old app
$settings = Settings::export('json', ['group' => 'shared']);

// Import to new app
// (copy file to new app)
Settings::import(file_get_contents('shared-settings.json'), 'json');
```

### Team Collaboration

```php
// Developer A exports their configuration
php artisan settings:export dev-config.json --group=development

// Developer B imports and modifies
php artisan settings:import dev-config.json
Settings::set('dev.local_api', 'http://localhost:3000');
```

---

[← Audit & History](audit-history.md) | [Events & Observers →](events-observers.md)
