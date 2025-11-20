# Basic Usage

Learn the fundamentals of working with Laravel Settings. This guide covers all basic CRUD operations, helpers, and Blade directives.

## Table of Contents

- [Setting Values](#setting-values)
- [Getting Values](#getting-values)
- [Checking Existence](#checking-existence)
- [Deleting Settings](#deleting-settings)
- [Working with Groups](#working-with-groups)
- [Helpers](#helpers)
- [Blade Directives](#blade-directives)
- [Best Practices](#best-practices)

## Setting Values

### Basic Set

Set a simple setting value:

```php
use Stratos\Settings\Facades\Settings;

Settings::set('site.name', 'My Application');
Settings::set('app.per_page', 15);
Settings::set('feature.dark_mode', true);
```

### Set with Group

Organize settings into logical groups:

```php
Settings::set('site_name', 'My Application', 'site');
Settings::set('smtp_host', 'smtp.mailtrap.io', 'email');
Settings::set('stripe_key', 'sk_test_...', 'api');
```

### Set with Encryption

Encrypt sensitive values like API keys:

```php
Settings::setEncrypted('api.stripe_key', 'sk_live_...');
Settings::setEncrypted('oauth.client_secret', 'secret123');
```

Encrypted values are automatically decrypted when retrieved.

### Set Multiple Settings

Use bulk operations for efficiency:

```php
Settings::setBulk([
    'site.name' => 'My Application',
    'site.description' => 'A great Laravel app',
    'site.email' => 'contact@example.com',
]);
```

## Getting Values

### Basic Get

Retrieve a setting value:

```php
$siteName = Settings::get('site.name');
$perPage = Settings::get('app.per_page');
$darkMode = Settings::get('feature.dark_mode');
```

### Get with Default

Provide a fallback value if the setting doesn't exist:

```php
$siteName = Settings::get('site.name', 'Default Application');
$perPage = Settings::get('app.per_page', 15);
$theme = Settings::get('app.theme', 'light');
```

### Get Encrypted Value

Encrypted values are automatically decrypted:

```php
$stripeKey = Settings::get('api.stripe_key');
// Returns decrypted value
```

Or use the dedicated method:

```php
$secret = Settings::encrypted('api.stripe_key');
```

### Get Multiple Settings

Retrieve several settings at once:

```php
$settings = Settings::getBulk([
    'site.name',
    'site.description',
    'site.email'
]);

// Returns:
// [
//     'site.name' => 'My Application',
//     'site.description' => 'A great Laravel app',
//     'site.email' => 'contact@example.com'
// ]
```

With defaults:

```php
$settings = Settings::getBulk([
    'site.name' => 'Default Name',
    'site.description' => 'Default Description',
]);
```

### Get All Settings

Retrieve all settings:

```php
$allSettings = Settings::all();
// Returns Collection with key => value pairs
```

Get all from specific group:

```php
$siteSettings = Settings::all('site');
$emailSettings = Settings::all('email');
```

### Get Settings by Group

```php
$siteSettings = Settings::group('site');
// Returns Collection of settings in the 'site' group
```

## Checking Existence

Check if a setting exists before using it:

```php
if (Settings::has('site.name')) {
    $name = Settings::get('site.name');
}

// Or with default
$name = Settings::has('site.name')
    ? Settings::get('site.name')
    : 'Default Name';

// Better: just use default parameter
$name = Settings::get('site.name', 'Default Name');
```

## Deleting Settings

### Delete Single Setting

```php
Settings::forget('site.name');

// Verify deletion
if (!Settings::has('site.name')) {
    // Setting was deleted
}
```

### Delete Multiple Settings

```php
Settings::forgetBulk([
    'site.name',
    'site.description',
    'site.email'
]);
```

### Delete All Settings in a Group

```php
$siteSettings = Settings::group('site');
foreach ($siteSettings as $setting) {
    Settings::forget($setting->key);
}
```

**Note**: Deleting a setting creates a history entry. You can still see when it was deleted and potentially restore it.

## Working with Groups

Groups help organize related settings together.

### Setting Keys with Groups

Use dot notation for implicit grouping:

```php
// These are in the 'site' group
Settings::set('site.name', 'My App');
Settings::set('site.description', 'Description');
Settings::set('site.logo', '/logo.png');

// These are in the 'email' group
Settings::set('email.smtp_host', 'smtp.mailtrap.io');
Settings::set('email.smtp_port', 587);
Settings::set('email.from_address', 'noreply@example.com');
```

### Explicit Group Assignment

```php
Settings::set('app_name', 'My Application', 'site');
Settings::set('maintenance', false, 'system');
```

### Retrieve by Group

```php
// Get all site settings
$siteSettings = Settings::group('site');

// Get all as key => value
$siteSettings = Settings::all('site');
```

### List Available Groups

```php
// If configured in config/settings.php
$groups = config('settings.groups');
// Returns: ['site' => 'Site Settings', 'email' => 'Email Settings', ...]
```

## Helpers

Laravel Settings provides convenient helper functions for quick access.

### setting() Helper

```php
// Get the settings manager instance
$manager = setting();

// Get a setting value
$name = setting('site.name');

// Get with default
$name = setting('site.name', 'Default Name');

// Set a value (via manager instance)
setting()->set('site.name', 'New Name');
```

### user_setting() Helper

```php
// Get user setting value
$theme = user_setting('theme');

// Get with default
$theme = user_setting('theme', 'light');

// Set a user setting (via manager instance)
user_setting()->set('theme', 'dark');
```

### Label and Description Helpers

Get translated labels and descriptions:

```php
// Get setting label
$label = setting_label('site.name');
$label = setting_label('site.name', 'en');

// Get setting description
$description = setting_description('max_users');

// User setting labels
$label = user_setting_label('theme');
$description = user_setting_description('theme');
```

## Blade Directives

### @setting Directive

Display setting values directly in Blade templates:

```blade
<!-- Simple usage -->
<h1>@setting('site.name')</h1>

<!-- In attributes -->
<meta name="description" content="@setting('site.description')">

<!-- With default (use null coalescing) -->
<h1>{{ setting('site.name') ?? 'Default Name' }}</h1>

<!-- Or use helper with default -->
<h1>{{ setting('site.name', 'Default Name') }}</h1>
```

### @userSetting Directive

Display user-specific settings:

```blade
<!-- User's theme preference -->
<body class="theme-@userSetting('theme')">

<!-- User's language -->
<html lang="@userSetting('language')">

<!-- With default -->
<div class="timezone-{{ user_setting('timezone', 'UTC') }}">
```

### Conditional Display

```blade
@if(setting('feature.maintenance_mode'))
    <div class="alert alert-warning">
        Site is under maintenance
    </div>
@endif

@if(user_setting('notifications.email'))
    <!-- Show email notification settings -->
@endif
```

### Loops

```blade
@foreach(Settings::group('social') as $setting)
    <a href="{{ $setting->value }}" class="social-link">
        {{ $setting->key }}
    </a>
@endforeach
```

## Best Practices

### 1. Use Consistent Naming

Choose a naming convention and stick to it:

```php
// Good: dot notation
Settings::set('site.name', 'My App');
Settings::set('site.description', 'Description');
Settings::set('email.from.address', 'noreply@example.com');

// Also good: snake_case with explicit groups
Settings::set('app_name', 'My App', 'site');
Settings::set('from_address', 'noreply@example.com', 'email');
```

### 2. Always Provide Defaults

Always use default values for reliability:

```php
// Good
$perPage = Settings::get('app.per_page', 15);

// Risky
$perPage = Settings::get('app.per_page'); // Could be null
```

### 3. Group Related Settings

Keep related settings together:

```php
// Site settings
Settings::set('site.name', 'My App');
Settings::set('site.description', 'Description');
Settings::set('site.logo', '/logo.png');

// Email settings
Settings::set('email.driver', 'smtp');
Settings::set('email.host', 'smtp.mailtrap.io');
Settings::set('email.port', 587);
```

### 4. Use Type-Appropriate Values

Let the package handle type casting:

```php
// Good
Settings::set('app.per_page', 15);          // Integer
Settings::set('feature.enabled', true);      // Boolean
Settings::set('allowed.ips', ['127.0.0.1']); // Array
Settings::set('metadata', ['key' => 'value']); // JSON

// Avoid
Settings::set('app.per_page', '15');         // String of number
Settings::set('feature.enabled', 'true');    // String of boolean
```

### 5. Encrypt Sensitive Data

Always encrypt sensitive information:

```php
// Good
Settings::setEncrypted('api.stripe_key', 'sk_live_...');
Settings::setEncrypted('oauth.client_secret', 'secret');

// Bad
Settings::set('api.stripe_key', 'sk_live_...'); // Not encrypted!
```

### 6. Use Bulk Operations

When setting multiple values, use bulk methods for better performance:

```php
// Good
Settings::setBulk([
    'site.name' => 'My App',
    'site.description' => 'Description',
    'site.email' => 'contact@example.com',
]);

// Less efficient
Settings::set('site.name', 'My App');
Settings::set('site.description', 'Description');
Settings::set('site.email', 'contact@example.com');
```

### 7. Validate Critical Settings

For important settings, add validation (see [Advanced Features](advanced-features.md)):

```php
Settings::setWithMetadata(
    key: 'max_users',
    value: 100,
    validationRules: ['required', 'integer', 'min:1', 'max:1000']
);
```

### 8. Clear Cache After Bulk Changes

When making many changes, clear the cache afterward:

```php
// Make changes
Settings::setBulk([...]);

// Clear cache to ensure fresh values
php artisan settings:clear-cache
```

## Common Patterns

### Feature Flags

```php
// Set feature flag
Settings::set('feature.new_dashboard', false);

// Check in code
if (Settings::get('feature.new_dashboard', false)) {
    return view('dashboard.new');
}
return view('dashboard.old');
```

### Maintenance Mode

```php
// Enable maintenance
Settings::set('site.maintenance_mode', true);

// Check in middleware
if (Settings::get('site.maintenance_mode', false)) {
    return response()->view('maintenance', [], 503);
}
```

### Configuration Override

```php
// Set dynamic email config
Settings::set('email.from.address', 'custom@example.com');

// Use in mail config
'from' => [
    'address' => Settings::get('email.from.address', env('MAIL_FROM_ADDRESS')),
    'name' => Settings::get('email.from.name', env('MAIL_FROM_NAME')),
],
```

## What's Next?

Now that you understand the basics, explore more advanced features:

- **[User Settings](user-settings.md)** - Manage per-user preferences
- **[Advanced Features](advanced-features.md)** - Validation, encryption, types, and more
- **[Permissions](permissions.md)** - Control who can view/edit settings
- **[API Reference](api-reference.md)** - Complete method documentation

---

[← Installation](installation.md) | [User Settings →](user-settings.md)
