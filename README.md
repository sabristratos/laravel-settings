# Laravel Settings Package

A flexible, extensible, and enterprise-ready settings management package for Laravel applications. Manage site-wide, system, and per-user settings with powerful features like caching, encryption, validation, audit trails, and more.

## Features

- **Multiple Setting Types**: Site/system-wide and user-specific settings
- **Type Casting**: Automatic type detection and casting (string, int, bool, array, json)
- **Encryption Support**: Securely store sensitive settings using Laravel's encryption
- **Caching**: Built-in caching support with auto-invalidation for optimal performance
- **Organized Groups**: Organize settings into logical groups
- **Multilingual Support**: Labels and descriptions in multiple languages
- **Validation Rules**: Per-setting validation with Laravel's validation system
- **Audit Trail**: Complete change history with rollback capability
- **Bulk Operations**: Set, get, or delete multiple settings at once
- **Import/Export**: Backup and migrate settings in JSON or YAML format
- **Events & Observers**: React to setting changes with events
- **REST API**: Optional API endpoints for external integrations
- **Interactive CLI**: Beautiful interactive commands using Laravel Prompts
- **Helpers & Directives**: Convenient helper functions and Blade directives
- **Comprehensive Tests**: 113 tests with full coverage using Pest
- **Laravel 12 Ready**: Built for Laravel 12 with modern PHP 8.3+ features

## Installation

### 1. Install the package via Composer

```bash
composer require strata/laravel-settings
```

### 2. Publish and run migrations

```bash
php artisan vendor:publish --provider="Strata\Settings\Providers\SettingsServiceProvider"
php artisan migrate
```

### 3. Publish configuration (optional)

```bash
php artisan vendor:publish --tag=settings-config
```

## Usage

### Site/System Settings

#### Using the Facade

```php
use Strata\Settings\Facades\Settings;

// Set a setting
Settings::set('site.name', 'My Application');
Settings::set('site.version', 1);
Settings::set('site.enabled', true);
Settings::set('site.features', ['api', 'webhooks', 'notifications']);

// Get a setting
$name = Settings::get('site.name');
$version = Settings::get('site.version', 0); // with default value

// Check if a setting exists
if (Settings::has('site.name')) {
    // ...
}

// Delete a setting
Settings::forget('site.name');

// Get all settings
$all = Settings::all();

// Get settings by group
$siteSettings = Settings::all('site');

// Clear cache
Settings::flush();
```

#### Using the Helper Function

```php
// Get a setting
$name = setting('site.name');
$version = setting('site.version', 0);

// Get the settings manager
$manager = setting();
```

#### Using Blade Directive

```blade
<h1>@setting('site.name')</h1>
<p>Version: @setting('site.version', '1.0')</p>
```

### Encrypted Settings

For sensitive data like API keys:

```php
// Set encrypted setting
Settings::setEncrypted('api.key', 'super-secret-key');

// Get encrypted setting
$apiKey = Settings::encrypted('api.key');
```

### User Settings

#### Using the Facade

```php
use Strata\Settings\Facades\Settings;

// For authenticated user
Settings::user()->set('theme', 'dark');
$theme = Settings::user()->get('theme');

// For specific user
$user = User::find(1);
Settings::user($user)->set('notifications', true);
$notifications = Settings::user($user)->get('notifications');

// Check existence
if (Settings::user()->has('theme')) {
    // ...
}

// Delete a user setting
Settings::user()->forget('theme');

// Get all user settings
$allUserSettings = Settings::user()->all();

// Delete all user settings
Settings::user()->flush();
```

#### Using the Helper Function

```php
// Get user setting (uses authenticated user)
$theme = user_setting('theme');
$notifications = user_setting('notifications', true);

// Get the user settings manager
$manager = user_setting();
```

#### Using Blade Directive

```blade
<div class="theme-@userSetting('theme', 'light')">
    <!-- Content -->
</div>
```

### Setting Groups

Organize settings into logical groups:

```php
Settings::set('email.driver', 'smtp', 'email');
Settings::set('email.host', 'smtp.example.com', 'email');
Settings::set('email.port', 587, 'email');

// Retrieve all email settings
$emailSettings = Settings::all('email');
```

### Type Casting

The package automatically handles type casting:

```php
// Integer
Settings::set('count', 42);
$count = Settings::get('count'); // Returns: 42 (int)

// Boolean
Settings::set('enabled', true);
$enabled = Settings::get('enabled'); // Returns: true (bool)

// Array
Settings::set('options', ['one', 'two', 'three']);
$options = Settings::get('options'); // Returns: ['one', 'two', 'three'] (array)

// The type is automatically detected and stored
```

## Configuration

The configuration file `config/settings.php` allows you to customize:

```php
return [
    // Cache configuration
    'cache' => [
        'enabled' => true,
        'driver' => config('cache.default'),
        'prefix' => 'settings',
        'ttl' => 3600, // 1 hour
    ],

    // Database table names
    'tables' => [
        'settings' => 'settings',
        'user_settings' => 'user_settings',
    ],

    // Encryption
    'encryption' => [
        'enabled' => true,
    ],

    // Default settings
    'defaults' => [
        'site.name' => 'My Application',
        'site.description' => 'A wonderful application',
    ],

    // Setting groups
    'groups' => [
        'site' => 'Site Settings',
        'system' => 'System Settings',
        'email' => 'Email Settings',
        'social' => 'Social Media',
        'seo' => 'SEO Settings',
    ],
];
```

## API Reference

### SettingsManager

#### Basic Methods

| Method | Description |
|--------|-------------|
| `get(string $key, mixed $default = null): mixed` | Get a setting value |
| `set(string $key, mixed $value, ?string $group = null, bool $encrypted = false): Setting` | Set a setting value |
| `has(string $key): bool` | Check if setting exists |
| `forget(string $key): bool` | Delete a setting |
| `all(?string $group = null): Collection` | Get all settings (optionally filtered by group) |
| `flush(): void` | Clear all cached settings |

#### Encryption Methods

| Method | Description |
|--------|-------------|
| `encrypted(string $key, mixed $default = null): mixed` | Get encrypted setting |
| `setEncrypted(string $key, mixed $value, ?string $group = null): Setting` | Set encrypted setting |

#### Bulk Operations

| Method | Description |
|--------|-------------|
| `setBulk(array $settings): void` | Set multiple settings at once |
| `getBulk(array $keys): array` | Get multiple settings at once |
| `forgetBulk(array $keys): void` | Delete multiple settings at once |
| `setWithMetadataBulk(array $settings): void` | Set multiple settings with metadata |

#### Metadata Methods

| Method | Description |
|--------|-------------|
| `setWithMetadata(string $key, mixed $value, ?string $group, array\|string\|null $label, array\|string\|null $description, ?array $validationRules, ?string $inputType, ?bool $isPublic, ?int $order, ?array $options, bool $encrypted): Setting` | Set setting with complete metadata |

#### Import/Export Methods

| Method | Description |
|--------|-------------|
| `export(string $format = 'json', ?string $group = null, bool $includeEncrypted = false, bool $includeMetadata = true): string` | Export settings to JSON or YAML |
| `import(string $data, string $format = 'json', bool $overwrite = false): void` | Import settings from JSON or YAML |

#### History Methods

| Method | Description |
|--------|-------------|
| `getHistory(string $key): Collection` | Get change history for a specific setting |
| `getAllHistory(): Collection` | Get all setting change history |
| `restoreToVersion(string $key, int $versionId): Setting` | Rollback setting to a specific version |

#### User Settings

| Method | Description |
|--------|-------------|
| `user($user = null): UserSettingsManager` | Get user settings manager |

### UserSettingsManager

| Method | Description |
|--------|-------------|
| `get(string $key, mixed $default = null): mixed` | Get user setting value |
| `set(string $key, mixed $value): UserSetting` | Set user setting value |
| `has(string $key): bool` | Check if user setting exists |
| `forget(string $key): bool` | Delete user setting |
| `all(): Collection` | Get all user settings |
| `flush(): bool` | Delete all user settings |
| `forUser(Authenticatable $user): static` | Set user for manager |
| `setEncrypted(string $key, mixed $value): UserSetting` | Set encrypted user setting |
| `encrypted(string $key, mixed $default = null): mixed` | Get encrypted user setting |

## Testing

The package includes comprehensive tests using Pest:

```bash
cd packages/strata/laravel-settings
composer install
vendor/bin/pest
```

## Use Cases

### 1. Site Configuration

```php
Settings::set('site.name', 'My App', 'site');
Settings::set('site.tagline', 'The best app ever', 'site');
Settings::set('site.maintenance_mode', false, 'site');
```

### 2. User Preferences

```php
// User theme preference
Settings::user()->set('theme', 'dark');

// User notification preferences
Settings::user()->set('email_notifications', true);
Settings::user()->set('push_notifications', false);
```

### 3. API Configuration

```php
Settings::setEncrypted('stripe.secret_key', env('STRIPE_SECRET'));
Settings::set('stripe.public_key', env('STRIPE_PUBLIC'), 'api');
```

### 4. Feature Flags

```php
Settings::set('features.new_dashboard', true, 'features');
Settings::set('features.beta_program', false, 'features');

// In your blade
@if(setting('features.new_dashboard'))
    <x-new-dashboard />
@else
    <x-old-dashboard />
@endif
```

## Artisan Commands

The package includes powerful CLI commands with interactive modes using Laravel Prompts.

### settings:get

Get a setting value from the command line.

```bash
# Get a setting
php artisan settings:get site.name

# With default value if not found
php artisan settings:get site.tagline --default="Welcome"
```

### settings:set

Set a setting value with optional interactive mode.

```bash
# Set directly with arguments
php artisan settings:set site.name "My Application"

# Set with group
php artisan settings:set api.key "secret-key" --group=api

# Set encrypted
php artisan settings:set stripe.secret "sk_live_..." --encrypted

# Interactive mode (prompts for all values)
php artisan settings:set
```

**Interactive Mode Features:**
- Smart type detection (booleans, integers, floats, arrays/JSON)
- Group selection from config or custom entry
- Encryption option with security hints
- Beautiful summary preview before saving
- Validation with helpful error messages

### settings:create

Create a new setting with complete metadata using an interactive wizard.

```bash
php artisan settings:create
```

**Wizard Steps:**
1. Enter setting key (with uniqueness validation)
2. Enter setting value (auto-detects type)
3. Choose to add metadata
4. Add labels (simple or multilingual)
5. Add description
6. Select input type (text, email, select, etc.)
7. Define validation rules
8. Set public/private visibility
9. Set display order
10. Select or enter group
11. Choose encryption
12. Review summary and confirm

### settings:list

List all settings with optional filtering.

```bash
# List all settings
php artisan settings:list

# List by group
php artisan settings:list --group=email

# List public settings only
php artisan settings:list --public

# List encrypted settings
php artisan settings:list --encrypted
```

### settings:export

Export settings to JSON or YAML format.

```bash
# Export to JSON
php artisan settings:export settings.json

# Export to YAML
php artisan settings:export settings.yaml

# Export specific group
php artisan settings:export settings.json --group=email

# Include encrypted settings (not recommended)
php artisan settings:export settings.json --include-encrypted

# Include metadata
php artisan settings:export settings.json --with-metadata
```

### settings:import

Import settings from JSON or YAML file.

```bash
# Import from JSON
php artisan settings:import settings.json

# Import from YAML
php artisan settings:import settings.yaml

# Overwrite existing settings
php artisan settings:import settings.json --force

# Import only specific group
php artisan settings:import settings.json --group=email
```

### settings:clear-cache

Clear all cached settings.

```bash
php artisan settings:clear-cache
```

## Advanced Features

### Bulk Operations

Efficiently set, get, or delete multiple settings at once.

```php
use Strata\Settings\Facades\Settings;

// Set multiple settings
Settings::setBulk([
    'site.name' => 'My App',
    'site.email' => 'hello@example.com',
    'site.phone' => '+1234567890',
]);

// Get multiple settings
$settings = Settings::getBulk(['site.name', 'site.email', 'site.phone']);
// Returns: ['site.name' => 'My App', 'site.email' => '...', ...]

// Delete multiple settings
Settings::forgetBulk(['old.setting1', 'old.setting2']);

// Set with metadata in bulk
Settings::setWithMetadataBulk([
    'site.name' => [
        'value' => 'My App',
        'label' => ['en' => 'Site Name', 'fr' => 'Nom du site'],
        'validation_rules' => ['required', 'string', 'max:255'],
        'is_public' => true,
    ],
    'site.email' => [
        'value' => 'hello@example.com',
        'label' => ['en' => 'Contact Email'],
        'validation_rules' => ['required', 'email'],
    ],
]);
```

### Validation

Settings can have validation rules that are automatically applied.

```php
use Strata\Settings\Facades\Settings;

// Set with validation rules
Settings::setWithMetadata(
    key: 'site.email',
    value: 'admin@example.com',
    validationRules: ['required', 'email', 'max:255']
);

// Validation happens automatically when setting values
try {
    Settings::set('site.email', 'invalid-email'); // Throws ValidationException
} catch (\Illuminate\Validation\ValidationException $e) {
    // Handle validation errors
}

// Get validation rules for a setting
$setting = Setting::key('site.email')->first();
$rules = $setting->validation_rules; // ['required', 'email', 'max:255']
```

### Multilingual Settings

Settings support labels, descriptions, and options in multiple languages.

```php
use Strata\Settings\Facades\Settings;

// Set multilingual labels and descriptions
Settings::setWithMetadata(
    key: 'site.name',
    value: 'My Application',
    label: [
        'en' => 'Site Name',
        'fr' => 'Nom du site',
    ],
    description: [
        'en' => 'The name of your website',
        'fr' => 'Le nom de votre site web',
    ]
);

// Get label in current locale
app()->setLocale('fr');
$label = setting_label('site.name'); // Returns: "Nom du site"

// Get description in current locale
$description = setting_description('site.name'); // Returns: "Le nom de votre site web"

// Configure supported locales in config/settings.php
'locales' => ['en', 'fr', 'de', 'es'],
'default_locale' => 'en',
```

### Audit Trail & History

Track all setting changes with rollback capability.

```php
use Strata\Settings\Facades\Settings;

// Enable audit trail in config
'audit' => [
    'enabled' => true,
    'track_ip' => true,
    'track_user_agent' => true,
],

// Get change history for a setting
$history = Settings::getHistory('site.name');
foreach ($history as $change) {
    echo $change->old_value; // Previous value
    echo $change->new_value; // New value
    echo $change->changed_by; // User ID who made the change
    echo $change->ip_address; // IP address
    echo $change->user_agent; // User agent
    echo $change->created_at; // When the change was made
}

// Get all history
$allHistory = Settings::getAllHistory();

// Rollback to a specific version
Settings::restoreToVersion('site.name', $versionId);
```

### Events & Observers

React to setting changes with Laravel events.

```php
// Available Events
use Strata\Settings\Events\SettingCreated;
use Strata\Settings\Events\SettingUpdated;
use Strata\Settings\Events\SettingDeleted;
use Strata\Settings\Events\UserSettingCreated;
use Strata\Settings\Events\UserSettingUpdated;
use Strata\Settings\Events\UserSettingDeleted;

// Listen to events
Event::listen(SettingUpdated::class, function ($event) {
    $setting = $event->setting;
    Log::info("Setting {$setting->key} was updated to {$setting->value}");
});

// Or create a listener
php artisan make:listener LogSettingChanges --event=SettingUpdated

// Observers automatically handle cache clearing
// No manual cache management needed!
```

### REST API

Optional REST API endpoints for external integrations.

```php
// Enable in config/settings.php
'api' => [
    'enabled' => true,
    'prefix' => 'api/settings',
    'middleware' => ['api', 'auth:sanctum'],
],
```

**Available Endpoints:**

```bash
# List all settings
GET /api/settings

# Get specific setting
GET /api/settings/{key}

# Create setting
POST /api/settings
{
    "key": "site.name",
    "value": "My App",
    "group": "site",
    "encrypted": false
}

# Update setting
PUT /api/settings/{key}
{
    "value": "Updated Value"
}

# Delete setting
DELETE /api/settings/{key}
```

**Example Usage:**

```javascript
// Using fetch
const response = await fetch('/api/settings/site.name', {
    headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json',
    }
});

const setting = await response.json();
console.log(setting.data.value);
```

### Middleware

Automatically share settings with all views.

```php
// Enable in config/settings.php
'share' => [
    'enabled' => true,
    'keys' => [], // Empty = share all
    'groups' => ['site'], // Only share 'site' group
    'public_only' => true, // Only share public settings
],

// Apply middleware to routes
Route::middleware('share-settings')->group(function () {
    Route::get('/', [HomeController::class, 'index']);
});

// Access in Blade views
<h1>{{ $settings['site.name'] }}</h1>
<p>{{ $settings['site.description'] }}</p>
```

### Import/Export Formats

**JSON Format:**

```json
{
    "settings": [
        {
            "key": "site.name",
            "value": "My Application",
            "type": "string",
            "group": "site",
            "encrypted": false,
            "metadata": {
                "label": {"en": "Site Name"},
                "validation_rules": ["required", "string", "max:255"]
            }
        }
    ]
}
```

**YAML Format:**

```yaml
settings:
  - key: site.name
    value: My Application
    type: string
    group: site
    encrypted: false
    metadata:
      label:
        en: Site Name
      validation_rules:
        - required
        - string
        - max:255
```

## License

MIT License

## Credits

Built by Strata
