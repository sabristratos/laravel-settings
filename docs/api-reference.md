# API Reference

Complete API documentation for all classes, methods, and helpers in Laravel Settings.

## Table of Contents

- [SettingsManager](#settingsmanager)
- [UserSettingsManager](#usersettingsmanager)
- [Setting Model](#setting-model)
- [UserSetting Model](#usersetting-model)
- [SettingHistory Model](#settinghistory-model)
- [Facades](#facades)
- [Helpers](#helpers)
- [Blade Directives](#blade-directives)

## SettingsManager

Namespace: `Stratos\Settings\Managers\SettingsManager`

### Basic Operations

| Method | Parameters | Return | Description |
|--------|-----------|--------|-------------|
| `set()` | `string $key, mixed $value, ?string $group = null, bool $encrypted = false` | `Setting` | Create or update a setting |
| `get()` | `string $key, mixed $default = null` | `mixed` | Get a setting value |
| `has()` | `string $key` | `bool` | Check if setting exists |
| `forget()` | `string $key` | `bool` | Delete a setting |
| `all()` | `?string $group = null` | `Collection` | Get all settings as key => value |
| `group()` | `?string $group = null` | `Collection` | Get settings in a group |

### Bulk Operations

| Method | Parameters | Return | Description |
|--------|-----------|--------|-------------|
| `setBulk()` | `array $settings` | `void` | Set multiple settings |
| `getBulk()` | `array $keys, mixed $default = null` | `array` | Get multiple settings |
| `forgetBulk()` | `array $keys` | `void` | Delete multiple settings |

### Encrypted Settings

| Method | Parameters | Return | Description |
|--------|-----------|--------|-------------|
| `setEncrypted()` | `string $key, mixed $value, ?string $group = null` | `Setting` | Set encrypted value |
| `encrypted()` | `string $key, mixed $default = null` | `mixed` | Get encrypted value |

### Metadata Operations

| Method | Parameters | Return | Description |
|--------|-----------|--------|-------------|
| `setWithMetadata()` | `string $key, mixed $value, ...` | `Setting` | Set with full metadata |
| `setWithMetadataBulk()` | `array $settings` | `void` | Bulk set with metadata |
| `allWithMetadata()` | `?string $group = null` | `Collection` | Get all with metadata |
| `allPublic()` | `?string $group = null` | `Collection` | Get only public settings |
| `getLabel()` | `string $key, ?string $locale = null` | `?string` | Get translated label |
| `getDescription()` | `string $key, ?string $locale = null` | `?string` | Get translated description |

### Permissions

| Method | Parameters | Return | Description |
|--------|-----------|--------|-------------|
| `setPermissions()` | `string $key, string $viewType, array $viewPermissions, string $editType, array $editPermissions` | `void` | Set permissions |

### History & Audit

| Method | Parameters | Return | Description |
|--------|-----------|--------|-------------|
| `getHistory()` | `string $key, int $limit = 50` | `Collection` | Get change history |
| `getAllHistory()` | `int $limit = 100` | `Collection` | Get all history |
| `restoreToVersion()` | `string $key, int $historyId` | `void` | Restore to version |

### Import/Export

| Method | Parameters | Return | Description |
|--------|-----------|--------|-------------|
| `export()` | `string $format = 'json', array $options = []` | `string` | Export settings |
| `import()` | `string $data, string $format = 'json', array $options = []` | `array` | Import settings |

### Cache

| Method | Parameters | Return | Description |
|--------|-----------|--------|-------------|
| `flush()` | - | `void` | Clear all cache |

## UserSettingsManager

Namespace: `Stratos\Settings\Managers\UserSettingsManager`

Identical methods to `SettingsManager` but scoped to a specific user.

### Access

```php
Settings::user($user = null) // Returns UserSettingsManager instance
```

If no user provided, uses `auth()->user()`.

## Setting Model

Namespace: `Stratos\Settings\Models\Setting`

### Properties

| Property | Type | Description |
|----------|------|-------------|
| `key` | string | Unique setting key |
| `group` | ?string | Group name |
| `label` | ?array | Translated labels |
| `description` | ?array | Translated descriptions |
| `value` | ?string | Raw value |
| `type` | string | Data type (string, int, bool, array, json) |
| `encrypted` | bool | Is encrypted |
| `validation_rules` | ?array | Validation rules |
| `options` | ?array | Translated options |
| `input_type` | string | UI input type |
| `is_public` | bool | Publicly accessible |
| `order` | int | Display order |
| `view_permissions` | ?array | View permission names |
| `edit_permissions` | ?array | Edit permission names |
| `view_permission_type` | string | View permission type |
| `edit_permission_type` | string | Edit permission type |

### Methods

| Method | Parameters | Return | Description |
|--------|-----------|--------|-------------|
| `getCastedValue()` | - | `mixed` | Get type-casted value |
| `setCastedValue()` | `mixed $value` | `void` | Set value with type detection |
| `validate()` | `mixed $value` | `bool` | Validate value |
| `getValidationErrors()` | `mixed $value` | `?array` | Get validation errors |
| `canView()` | `?User $user` | `bool` | Check view permission |
| `canEdit()` | `?User $user` | `bool` | Check edit permission |
| `getTranslatedLabel()` | `?string $locale = null` | `?string` | Get label for locale |
| `getTranslatedDescription()` | `?string $locale = null` | `?string` | Get description for locale |
| `getTranslatedOptions()` | `?string $locale = null` | `?array` | Get options for locale |

### Scopes

| Scope | Parameters | Description |
|-------|-----------|-------------|
| `scopeGroup()` | `$query, string $group` | Filter by group |
| `scopeKey()` | `$query, string $key` | Find by key |
| `scopePublic()` | `$query` | Only public settings |
| `scopeEncrypted()` | `$query` | Only encrypted settings |
| `scopeOrdered()` | `$query` | Order by order column |

## UserSetting Model

Namespace: `Stratos\Settings\Models\UserSetting`

Similar to `Setting` model but:
- Has `user_id` foreign key
- Belongs to `User` via `user()` relationship
- No permission-related fields (`is_public`, `view_permissions`, etc.)

### Additional Relationship

| Method | Return | Description |
|--------|--------|-------------|
| `user()` | `BelongsTo` | User who owns this setting |

## SettingHistory Model

Namespace: `Stratos\Settings\Models\SettingHistory`

### Properties

| Property | Type | Description |
|----------|------|-------------|
| `setting_key` | string | Setting key that was changed |
| `old_value` | ?string | Previous value |
| `new_value` | ?string | New value |
| `old_type` | ?string | Previous type |
| `new_type` | ?string | New type |
| `user_id` | ?int | User who made change |
| `ip_address` | ?string | IP address |
| `user_agent` | ?string | Browser/client info |
| `action` | string | 'created', 'updated', or 'deleted' |

### Relationships

| Method | Return | Description |
|--------|--------|-------------|
| `user()` | `BelongsTo` | User who made the change |

## Facades

### Settings Facade

Namespace: `Stratos\Settings\Facades\Settings`

Provides static access to `SettingsManager`:

```php
use Stratos\Settings\Facades\Settings;

Settings::set('key', 'value');
Settings::get('key');
Settings::user()->set('key', 'value');
```

## Helpers

### setting()

```php
function setting(?string $key = null, mixed $default = null): mixed
```

- No parameters: Returns `SettingsManager` instance
- With key: Returns setting value or default

**Examples:**

```php
setting('site.name'); // Get value
setting('site.name', 'Default'); // With default
setting()->set('key', 'value'); // Set via manager
```

### user_setting()

```php
function user_setting(?string $key = null, mixed $default = null): mixed
```

- No parameters: Returns `UserSettingsManager` instance
- With key: Returns user setting value or default

**Examples:**

```php
user_setting('theme'); // Get value
user_setting('theme', 'light'); // With default
user_setting()->set('theme', 'dark'); // Set via manager
```

### setting_label()

```php
function setting_label(string $key, ?string $locale = null): ?string
```

Get translated label for a setting.

**Examples:**

```php
setting_label('max_users'); // Current locale
setting_label('max_users', 'en'); // Specific locale
```

### setting_description()

```php
function setting_description(string $key, ?string $locale = null): ?string
```

Get translated description for a setting.

### user_setting_label()

```php
function user_setting_label(string $key, ?string $locale = null): ?string
```

Get translated label for a user setting.

### user_setting_description()

```php
function user_setting_description(string $key, ?string $locale = null): ?string
```

Get translated description for a user setting.

## Blade Directives

### @setting

Display a setting value in Blade templates.

**Syntax:**

```blade
@setting('key')
```

**Examples:**

```blade
<h1>@setting('site.name')</h1>
<meta content="@setting('site.description')">
```

### @userSetting

Display a user setting value in Blade templates.

**Syntax:**

```blade
@userSetting('key')
```

**Examples:**

```blade
<body class="theme-@userSetting('theme')">
<html lang="@userSetting('language')">
```

---

[← Artisan Commands](artisan-commands.md) | [REST API →](rest-api.md)
