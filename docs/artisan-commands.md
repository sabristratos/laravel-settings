# Artisan Commands

Laravel Settings provides comprehensive CLI commands for managing settings from the terminal.

## Table of Contents

- [settings:get](#settingsget)
- [settings:set](#settingsset)
- [settings:create](#settingscreate)
- [settings:list](#settingslist)
- [settings:export](#settingsexport)
- [settings:import](#settingsimport)
- [settings:clear-cache](#settingsclear-cache)

## settings:get

Get a setting value.

### Syntax

```bash
php artisan settings:get {key} {--user=}
```

### Examples

```bash
# Get a global setting
php artisan settings:get site.name

# Get a user setting
php artisan settings:get theme --user=1

# Non-existent setting returns null
php artisan settings:get does.not.exist
```

### Output

```
My Application
```

## settings:set

Set a setting value.

### Syntax

```bash
php artisan settings:set {key} {value} {--group=} {--encrypted} {--user=}
```

### Examples

```bash
# Set a global setting
php artisan settings:set site.name "My Application"

# Set with group
php artisan settings:set smtp_host "smtp.mailtrap.io" --group=email

# Set encrypted value
php artisan settings:set api.stripe_key "sk_live_..." --encrypted

# Set user setting
php artisan settings:set theme dark --user=1

# Set boolean
php artisan settings:set maintenance_mode true

# Set number
php artisan settings:set max_users 100

# Set array (JSON)
php artisan settings:set allowed_ips '["127.0.0.1","192.168.1.1"]'
```

### Output

```
Setting [site.name] set to [My Application]
```

## settings:create

Interactively create a setting with full metadata.

### Syntax

```bash
php artisan settings:create
```

### Interactive Wizard

```bash
php artisan settings:create

# Prompts:
> Setting Key: site.name
> Value: My Application
> Type (string/int/bool/array/json): string
> Group (optional): site
> Label (en): Site Name
> Description (en): The name of the application
> Input Type (text/textarea/number/email/select/checkbox): text
> Validation Rules (comma-separated, optional): required,string,max:255
> Is Public? (yes/no): yes
> Order (default 0): 1

✓ Setting created successfully
```

## settings:list

List all settings or settings in a specific group.

### Syntax

```bash
php artisan settings:list {--group=} {--format=table|json}
```

### Examples

```bash
# List all settings
php artisan settings:list

# List settings in a group
php artisan settings:list --group=site

# Output as JSON
php artisan settings:list --format=json

# List user settings
php artisan settings:list --user=1
```

### Output (Table)

```
+---------------+------------------+--------+-------+
| Key           | Value            | Type   | Group |
+---------------+------------------+--------+-------+
| site.name     | My Application   | string | site  |
| site.email    | info@example.com | string | site  |
| max_users     | 100              | int    | null  |
| maintenance   | false            | bool   | system|
+---------------+------------------+--------+-------+
```

### Output (JSON)

```json
[
  {
    "key": "site.name",
    "value": "My Application",
    "type": "string",
    "group": "site"
  },
  {
    "key": "max_users",
    "value": 100,
    "type": "int",
    "group": null
  }
]
```

## settings:export

Export settings to a file.

### Syntax

```bash
php artisan settings:export {file} {--format=json|yaml} {--group=} {--with-metadata} {--exclude-encrypted}
```

### Examples

```bash
# Export all settings to JSON
php artisan settings:export settings.json

# Export to YAML
php artisan settings:export settings.yaml --format=yaml

# Export specific group
php artisan settings:export site-settings.json --group=site

# Include metadata (labels, descriptions, validation)
php artisan settings:export full-backup.json --with-metadata

# Exclude encrypted values
php artisan settings:export safe-export.json --exclude-encrypted

# Combine options
php artisan settings:export backup.json --group=api --with-metadata --exclude-encrypted
```

### Output

```
Settings exported successfully to [settings.json]
Total settings exported: 24
```

## settings:import

Import settings from a file.

### Syntax

```bash
php artisan settings:import {file} {--format=json|yaml} {--overwrite} {--dry-run}
```

### Examples

```bash
# Import from JSON
php artisan settings:import settings.json

# Import from YAML
php artisan settings:import settings.yaml --format=yaml

# Overwrite existing settings
php artisan settings:import settings.json --overwrite

# Dry run (test import without applying)
php artisan settings:import settings.json --dry-run

# Import and overwrite
php artisan settings:import production-settings.json --overwrite
```

### Output

```
Importing settings from [settings.json]...
✓ site.name (created)
✓ site.email (created)
! max_users (skipped - already exists)
✓ maintenance_mode (created)

Summary:
- Created: 3
- Updated: 0
- Skipped: 1
```

### Dry Run Output

```
DRY RUN - No changes will be made

Would import:
✓ site.name (would create)
✓ site.email (would create)
! max_users (would skip - already exists)
```

## settings:clear-cache

Clear the settings cache.

### Syntax

```bash
php artisan settings:clear-cache {--user=}
```

### Examples

```bash
# Clear all settings cache
php artisan settings:clear-cache

# Clear specific user's cache
php artisan settings:clear-cache --user=1
```

### Output

```
Settings cache cleared successfully
```

## Common Workflows

### Deploy New Settings to Production

```bash
# On development
php artisan settings:export production-settings.json --group=production

# Copy file to production server
scp production-settings.json server:/path/

# On production
php artisan settings:import production-settings.json --overwrite
php artisan settings:clear-cache
```

### Backup Before Major Changes

```bash
# Create backup
php artisan settings:export backup-$(date +%Y%m%d).json --with-metadata

# Make changes
php artisan settings:set api.provider new-provider

# If something goes wrong
php artisan settings:import backup-20240115.json --overwrite
```

### List and Inspect Settings

```bash
# See all settings
php artisan settings:list

# Check specific value
php artisan settings:get api.rate_limit

# List API settings
php artisan settings:list --group=api

# Export for inspection
php artisan settings:export inspect.json --format=json
cat inspect.json | jq .
```

---

[← Events & Observers](events-observers.md) | [API Reference →](api-reference.md)
