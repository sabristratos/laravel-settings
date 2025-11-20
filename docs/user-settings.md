# User Settings

User Settings allow you to store preferences and configuration specific to individual users, such as theme preferences, language choices, notification settings, and dashboard layouts.

## Table of Contents

- [When to Use User Settings](#when-to-use-user-settings)
- [User Settings vs Global Settings](#user-settings-vs-global-settings)
- [Basic Operations](#basic-operations)
- [User Settings Manager API](#user-settings-manager-api)
- [Common Use Cases](#common-use-cases)
- [Best Practices](#best-practices)

## When to Use User Settings

Use **User Settings** for:
- Theme preferences (dark mode, light mode)
- Language/locale preferences
- Timezone settings
- Notification preferences
- Dashboard layout customization
- Per-user feature flags
- Display density (compact, comfortable, spacious)
- Sidebar collapsed/expanded state
- Items per page in listings

Use **Global Settings** for:
- Application-wide configuration
- Site name, logo, description
- API credentials
- Email configuration
- Feature flags affecting all users
- System-wide maintenance mode

## User Settings vs Global Settings

| Feature | Global Settings | User Settings |
|---------|----------------|---------------|
| **Scope** | Application-wide | Per-user |
| **Table** | `settings` | `user_settings` |
| **Access** | `Settings::get()` | `Settings::user()->get()` |
| **Helper** | `setting()` | `user_setting()` |
| **Blade** | `@setting()` | `@userSetting()` |
| **Permissions** | Yes | No (always owned by user) |
| **Audit Trail** | Yes | Yes |
| **Encryption** | Yes | Yes |
| **Validation** | Yes | Yes |

## Basic Operations

### Setting User Preferences

```php
use Stratos\Settings\Facades\Settings;

// Set for current authenticated user
Settings::user()->set('theme', 'dark');
Settings::user()->set('language', 'en');
Settings::user()->set('timezone', 'America/New_York');

// Set for specific user
$user = User::find(1);
Settings::user($user)->set('theme', 'dark');
```

### Getting User Preferences

```php
// Get for current user
$theme = Settings::user()->get('theme');
$language = Settings::user()->get('language', 'en');

// Get for specific user
$user = User::find(1);
$theme = Settings::user($user)->get('theme', 'light');
```

### Using the Helper

```php
// Get current user's setting
$theme = user_setting('theme');
$theme = user_setting('theme', 'light'); // with default

// Set current user's setting
user_setting()->set('theme', 'dark');
```

### Using in Blade

```blade
<!-- Display user setting -->
<body class="theme-@userSetting('theme')">

<!-- With default via helper -->
<body class="theme-{{ user_setting('theme', 'light') }}">

<!-- Conditional -->
@if(user_setting('sidebar.collapsed'))
    <aside class="sidebar collapsed">
@else
    <aside class="sidebar expanded">
@endif
```

### Checking Existence

```php
if (Settings::user()->has('theme')) {
    $theme = Settings::user()->get('theme');
}
```

### Deleting User Settings

```php
// Delete specific setting
Settings::user()->forget('theme');

// Delete for specific user
Settings::user($user)->forget('language');
```

## User Settings Manager API

The UserSettingsManager provides the same methods as SettingsManager, but scoped to a specific user.

### Available Methods

```php
// Basic CRUD
Settings::user($user)->set($key, $value, $group = null, $encrypted = false);
Settings::user($user)->get($key, $default = null);
Settings::user($user)->has($key);
Settings::user($user)->forget($key);
Settings::user($user)->all($group = null);

// Bulk operations
Settings::user($user)->setBulk($settings);
Settings::user($user)->getBulk($keys, $default = null);
Settings::user($user)->forgetBulk($keys);

// With metadata
Settings::user($user)->setWithMetadata(...);
Settings::user($user)->allWithMetadata($group = null);

// Encrypted
Settings::user($user)->setEncrypted($key, $value, $group = null);
Settings::user($user)->encrypted($key, $default = null);

// Labels & descriptions
Settings::user($user)->getLabel($key, $locale = null);
Settings::user($user)->getDescription($key, $locale = null);

// History
Settings::user($user)->getHistory($key, $limit = 50);
Settings::user($user)->getAllHistory($limit = 100);
Settings::user($user)->restoreToVersion($key, $historyId);

// Cache
Settings::user($user)->flush(); // Clear cache for this user
```

### Specifying the User

```php
// Use current authenticated user (default)
Settings::user()->set('theme', 'dark');

// Use specific user instance
$user = User::find(1);
Settings::user($user)->set('theme', 'dark');

// Use user ID
Settings::user(1)->set('theme', 'dark');

// In controller with DI
public function updatePreferences(Request $request)
{
    Settings::user($request->user())->setBulk([
        'theme' => $request->theme,
        'language' => $request->language,
        'timezone' => $request->timezone,
    ]);
}
```

## Common Use Cases

### Theme Preference

```php
// Controller
public function setTheme(Request $request)
{
    $theme = $request->input('theme', 'light');

    Settings::user()->set('theme', $theme);

    return back()->with('success', 'Theme updated');
}

// Blade layout
<body class="theme-{{ user_setting('theme', 'light') }}">
```

### Language/Locale Preference

```php
// Store user's language preference
Settings::user()->set('language', 'es');

// Middleware to apply user language
public function handle($request, Closure $next)
{
    if (auth()->check()) {
        $locale = user_setting('language', config('app.locale'));
        app()->setLocale($locale);
    }

    return $next($request);
}
```

### Notification Preferences

```php
// Store preferences
Settings::user()->setBulk([
    'notifications.email' => true,
    'notifications.sms' => false,
    'notifications.push' => true,
    'notifications.marketing' => false,
]);

// Check before sending notification
if (user_setting('notifications.email', true)) {
    // Send email notification
}
```

### Dashboard Customization

```php
// Store dashboard layout
Settings::user()->set('dashboard.widgets', [
    'sales' => ['position' => 1, 'size' => 'large'],
    'users' => ['position' => 2, 'size' => 'medium'],
    'revenue' => ['position' => 3, 'size' => 'small'],
]);

// Retrieve and render
$widgets = user_setting('dashboard.widgets', []);
foreach ($widgets as $widget => $config) {
    // Render widget
}
```

### Sidebar State

```php
// Store sidebar state
Settings::user()->set('sidebar.collapsed', true);

// Blade
<aside class="{{ user_setting('sidebar.collapsed') ? 'collapsed' : 'expanded' }}">
    <!-- Sidebar content -->
</aside>
```

### Pagination Preferences

```php
// Store preferred items per page
Settings::user()->set('pagination.per_page', 50);

// Use in queries
$perPage = user_setting('pagination.per_page', 15);
$users = User::paginate($perPage);
```

### Timezone Preference

```php
// Store timezone
Settings::user()->set('timezone', 'Europe/Paris');

// Use in date formatting
$timezone = user_setting('timezone', config('app.timezone'));
$date = $record->created_at->timezone($timezone)->format('Y-m-d H:i:s');
```

## Advanced Features for User Settings

### With Validation

```php
use Stratos\Settings\Facades\Settings;

Settings::user()->setWithMetadata(
    key: 'items_per_page',
    value: 25,
    validationRules: ['integer', 'min:10', 'max:100'],
    label: ['en' => 'Items Per Page', 'de' => 'Elemente pro Seite'],
    description: ['en' => 'Number of items to display per page']
);
```

### With Encryption

Encrypt sensitive user-specific data:

```php
// Encrypt user's API tokens
Settings::user()->setEncrypted('api_token', 'user-secret-token-123');

// Retrieve (automatically decrypted)
$token = Settings::user()->get('api_token');
```

### With Groups

Organize user settings by category:

```php
// Notification settings
Settings::user()->set('email_enabled', true, 'notifications');
Settings::user()->set('sms_enabled', false, 'notifications');

// Privacy settings
Settings::user()->set('profile_public', true, 'privacy');
Settings::user()->set('show_email', false, 'privacy');

// Get all from group
$notificationSettings = Settings::user()->all('notifications');
$privacySettings = Settings::user()->all('privacy');
```

### History and Rollback

Track changes to user settings:

```php
// View history
$history = Settings::user()->getHistory('theme');

foreach ($history as $change) {
    echo "Changed from {$change->old_value} to {$change->new_value}";
    echo " on {$change->created_at}";
}

// Rollback to previous version
Settings::user()->restoreToVersion('theme', $historyId);
```

## Best Practices

### 1. Always Provide Sensible Defaults

```php
// Good
$theme = user_setting('theme', 'light');
$perPage = user_setting('per_page', 15);

// Risky
$theme = user_setting('theme'); // Could be null
```

### 2. Use Groups for Organization

```php
// Good - organized by group
Settings::user()->setBulk([
    'notifications.email' => true,
    'notifications.sms' => false,
    'privacy.profile_public' => true,
    'privacy.show_email' => false,
]);

// Also good - explicit groups
Settings::user()->set('email', true, 'notifications');
Settings::user()->set('sms', false, 'notifications');
```

### 3. Validate User Input

```php
public function updatePreferences(Request $request)
{
    $validated = $request->validate([
        'theme' => 'in:light,dark',
        'language' => 'in:en,es,fr,de',
        'per_page' => 'integer|min:10|max:100',
    ]);

    Settings::user()->setBulk($validated);
}
```

### 4. Provide UI for User Settings

Create a dedicated preferences page:

```php
// Controller
public function edit()
{
    $settings = [
        'theme' => user_setting('theme', 'light'),
        'language' => user_setting('language', 'en'),
        'timezone' => user_setting('timezone', 'UTC'),
        'per_page' => user_setting('per_page', 15),
    ];

    return view('preferences.edit', compact('settings'));
}

public function update(Request $request)
{
    $validated = $request->validate([
        'theme' => 'in:light,dark',
        'language' => 'in:en,es,fr,de',
        'timezone' => 'timezone',
        'per_page' => 'integer|min:10|max:100',
    ]);

    Settings::user()->setBulk($validated);

    return back()->with('success', 'Preferences updated');
}
```

### 5. Use Middleware for Global Application

Apply user preferences application-wide:

```php
// app/Http/Middleware/ApplyUserPreferences.php
public function handle($request, Closure $next)
{
    if (auth()->check()) {
        // Apply language
        $locale = user_setting('language', config('app.locale'));
        app()->setLocale($locale);

        // Apply timezone
        $timezone = user_setting('timezone', config('app.timezone'));
        config(['app.user_timezone' => $timezone]);
    }

    return $next($request);
}
```

### 6. Cache User Settings

For frequently accessed user settings, consider caching:

```php
// The package automatically caches, but you can be explicit
$theme = Cache::remember(
    "user.{$userId}.theme",
    3600,
    fn() => Settings::user($userId)->get('theme', 'light')
);
```

### 7. Bulk Update for Performance

```php
// Good - single database transaction
Settings::user()->setBulk([
    'theme' => 'dark',
    'language' => 'en',
    'timezone' => 'UTC',
    'per_page' => 25,
]);

// Less efficient - multiple queries
Settings::user()->set('theme', 'dark');
Settings::user()->set('language', 'en');
Settings::user()->set('timezone', 'UTC');
Settings::user()->set('per_page', 25);
```

## Testing User Settings

```php
use Stratos\Settings\Facades\Settings;

test('user can update theme preference', function () {
    $user = User::factory()->create();

    Settings::user($user)->set('theme', 'dark');

    expect(Settings::user($user)->get('theme'))->toBe('dark');
});

test('user settings are isolated', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    Settings::user($user1)->set('theme', 'dark');
    Settings::user($user2)->set('theme', 'light');

    expect(Settings::user($user1)->get('theme'))->toBe('dark');
    expect(Settings::user($user2)->get('theme'))->toBe('light');
});
```

## What's Next?

- **[Advanced Features](advanced-features.md)** - Validation, encryption, types, caching
- **[Permissions](permissions.md)** - Control access to settings
- **[Recipes](recipes.md)** - More practical examples
- **[API Reference](api-reference.md)** - Complete method documentation

---

[← Basic Usage](usage.md) | [Advanced Features →](advanced-features.md)
