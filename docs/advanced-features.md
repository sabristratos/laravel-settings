# Advanced Features

Explore the advanced capabilities of Laravel Settings including type casting, validation, encryption, groups, caching, and multilingual support.

## Table of Contents

- [Type Casting](#type-casting)
- [Validation](#validation)
- [Encryption](#encryption)
- [Groups](#groups)
- [Caching](#caching)
- [Multilingual Support](#multilingual-support)
- [Metadata](#metadata)

## Type Casting

Laravel Settings automatically detects and casts values to appropriate types.

### Supported Types

- `string` - Text values
- `int` / `integer` - Whole numbers
- `float` - Decimal numbers
- `bool` / `boolean` - True/false
- `array` - PHP arrays
- `json` - JSON objects

### Automatic Type Detection

```php
use Stratos\Settings\Facades\Settings;

// Integer
Settings::set('max_users', 100);
$maxUsers = Settings::get('max_users'); // Returns: 100 (int)

// Boolean
Settings::set('maintenance_mode', true);
$maintenance = Settings::get('maintenance_mode'); // Returns: true (bool)

// Array
Settings::set('allowed_ips', ['127.0.0.1', '192.168.1.1']);
$ips = Settings::get('allowed_ips'); // Returns: array

// JSON
Settings::set('config', ['key' => 'value', 'nested' => ['data' => 123]]);
$config = Settings::get('config'); // Returns: array
```

### Manual Type Specification

```php
use Stratos\Settings\Models\Setting;

$setting = Setting::create([
    'key' => 'custom.value',
    'value' => '123',
    'type' => 'int', // Explicitly specify type
]);

$value = $setting->getCastedValue(); // Returns: 123 (int), not "123" (string)
```

### Type Coercion

Values are automatically converted to the stored type:

```php
// Setting stored as integer
Settings::set('per_page', 25);

// Even if queried as string, returns integer
$perPage = Settings::get('per_page'); // int(25)
```

## Validation

Add validation rules to settings to ensure data integrity.

### Basic Validation

```php
Settings::setWithMetadata(
    key: 'max_users',
    value: 100,
    validationRules: ['required', 'integer', 'min:1', 'max:1000']
);

// Attempting to set invalid value will fail
Settings::set('max_users', 5000); // Throws ValidationException
```

### Validation Rules Array

```php
Settings::setWithMetadata(
    key: 'email.from',
    value: 'noreply@example.com',
    validationRules: ['required', 'email']
);

Settings::setWithMetadata(
    key: 'allowed_domains',
    value: ['example.com', 'test.com'],
    validationRules: ['array', 'min:1']
);
```

### Checking Validation

```php
use Stratos\Settings\Models\Setting;

$setting = Setting::where('key', 'max_users')->first();

// Validate a value
if ($setting->validate(500)) {
    // Valid
}

// Get validation errors
$errors = $setting->getValidationErrors(5000);
if ($errors) {
    foreach ($errors as $error) {
        echo $error;
    }
}
```

### Complex Validation

```php
Settings::setWithMetadata(
    key: 'api.rate_limit',
    value: 60,
    validationRules: [
        'required',
        'integer',
        'min:10',
        'max:10000',
        'multiple_of:10'
    ]
);
```

## Encryption

Securely store sensitive data using Laravel's encryption.

### Encrypting Values

```php
// Method 1: Using setEncrypted
Settings::setEncrypted('api.stripe_secret', 'sk_live_...');

// Method 2: Using set with encrypted flag
Settings::set('oauth.client_secret', 'secret', null, true);

// Method 3: Using setWithMetadata
Settings::setWithMetadata(
    key: 'api.aws_secret',
    value: 'secret_key_123',
    encrypted: true
);
```

### Retrieving Encrypted Values

Values are automatically decrypted when retrieved:

```php
// Both return the decrypted value
$secret = Settings::get('api.stripe_secret');
$secret = Settings::encrypted('api.stripe_secret');
```

### Encryption in User Settings

```php
// Encrypt user-specific sensitive data
Settings::user()->setEncrypted('api_token', 'user-token-123');

// Retrieve (automatically decrypted)
$token = Settings::user()->get('api_token');
```

### Security Best Practices

```php
// Always encrypt sensitive data
Settings::setEncrypted('api.stripe_secret', env('STRIPE_SECRET'));
Settings::setEncrypted('api.aws_secret', env('AWS_SECRET_KEY'));
Settings::setEncrypted('oauth.client_secret', env('OAUTH_SECRET'));
Settings::setEncrypted('database.password', env('DB_PASSWORD'));

// Don't encrypt non-sensitive data (unnecessary overhead)
Settings::set('site.name', 'My Application'); // No encryption needed
Settings::set('theme', 'dark'); // No encryption needed
```

## Groups

Organize related settings into logical groups for better management.

### Setting with Groups

```php
// Method 1: Dot notation (implicit grouping)
Settings::set('site.name', 'My App');
Settings::set('site.logo', '/logo.png');
Settings::set('site.description', 'A great app');

// Method 2: Explicit group parameter
Settings::set('smtp_host', 'smtp.mailtrap.io', 'email');
Settings::set('smtp_port', 587, 'email');
Settings::set('from_address', 'noreply@example.com', 'email');
```

### Retrieving by Group

```php
// Get all settings in a group
$siteSettings = Settings::group('site');
$emailSettings = Settings::group('email');

// Get all as key => value pairs
$siteSettings = Settings::all('site');
// Returns: ['site.name' => 'My App', 'site.logo' => '/logo.png', ...]
```

### Group Names Configuration

Define human-readable group names in `config/settings.php`:

```php
'groups' => [
    'site' => 'Site Settings',
    'email' => 'Email Configuration',
    'api' => 'API Credentials',
    'social' => 'Social Media',
    'seo' => 'SEO Settings',
    'system' => 'System Configuration',
],
```

### Use Cases for Groups

```php
// Email configuration group
Settings::setBulk([
    'email.driver' => 'smtp',
    'email.host' => 'smtp.mailtrap.io',
    'email.port' => 587,
    'email.username' => 'user',
    'email.password' => 'pass',
    'email.encryption' => 'tls',
]);

// Social media group
Settings::setBulk([
    'social.facebook' => 'https://facebook.com/page',
    'social.twitter' => 'https://twitter.com/handle',
    'social.instagram' => 'https://instagram.com/profile',
]);

// Retrieve all email settings
$emailConfig = Settings::all('email');
```

## Caching

Laravel Settings includes intelligent caching for optimal performance.

### Cache Configuration

In `config/settings.php`:

```php
'cache' => [
    'enabled' => true,
    'driver' => 'redis', // or 'file', 'memcached', etc.
    'prefix' => 'settings',
    'ttl' => 3600, // 1 hour
],
```

### Automatic Cache Management

Cache is automatically managed:

```php
// Setting a value automatically caches it
Settings::set('site.name', 'My App');
// Cached with key: "settings:site.name"

// Getting uses cache
$name = Settings::get('site.name');
// Retrieved from cache if available

// Updating invalidates cache
Settings::set('site.name', 'New Name');
// Cache automatically cleared and refreshed
```

### Manual Cache Control

```php
// Clear all settings cache
Settings::flush();

// Clear cache via Artisan
php artisan settings:clear-cache

// Clear specific user's cache
Settings::user($user)->flush();
```

### Cache Keys

Cache keys follow this pattern:

```
settings:{key}           // Global settings
settings:user:{userId}:{key}  // User settings
settings:exists:{key}    // Existence checks
```

### Disable Caching

```php
// In config/settings.php
'cache' => [
    'enabled' => false,
],

// Or via environment
SETTINGS_CACHE_ENABLED=false
```

### Performance Tips

```php
// Good: Use bulk operations (single cache operation)
$settings = Settings::getBulk(['site.name', 'site.logo', 'site.description']);

// Less efficient: Multiple cache lookups
$name = Settings::get('site.name');
$logo = Settings::get('site.logo');
$description = Settings::get('site.description');
```

## Multilingual Support

Store labels, descriptions, and options in multiple languages.

### Setting Labels and Descriptions

```php
Settings::setWithMetadata(
    key: 'max_users',
    value: 100,
    label: [
        'en' => 'Maximum Users',
        'es' => 'Usuarios Máximos',
        'fr' => 'Utilisateurs Maximum',
        'de' => 'Maximale Benutzer',
    ],
    description: [
        'en' => 'Maximum number of users allowed',
        'es' => 'Número máximo de usuarios permitidos',
        'fr' => 'Nombre maximum d\'utilisateurs autorisés',
        'de' => 'Maximale Anzahl erlaubter Benutzer',
    ]
);
```

### Retrieving Translations

```php
// Get label for current locale
$label = Settings::getLabel('max_users');

// Get label for specific locale
$labelEn = Settings::getLabel('max_users', 'en');
$labelEs = Settings::getLabel('max_users', 'es');

// Get description
$description = Settings::getDescription('max_users');
$descriptionDe = Settings::getDescription('max_users', 'de');
```

### Using Helpers

```php
// Get translated label
$label = setting_label('max_users');
$label = setting_label('max_users', 'fr');

// Get translated description
$description = setting_description('max_users');

// User settings
$label = user_setting_label('theme');
$label = user_setting_label('theme', 'es');
```

### Translatable Options

For settings with options (like select dropdowns):

```php
Settings::setWithMetadata(
    key: 'theme',
    value: 'dark',
    label: [
        'en' => 'Theme',
        'es' => 'Tema',
    ],
    options: [
        'light' => [
            'en' => 'Light',
            'es' => 'Claro',
        ],
        'dark' => [
            'en' => 'Dark',
            'es' => 'Oscuro',
        ],
        'auto' => [
            'en' => 'Auto',
            'es' => 'Automático',
        ],
    ]
);

// Retrieve translated options
$setting = Setting::where('key', 'theme')->first();
$options = $setting->getTranslatedOptions('es');
// Returns: ['light' => 'Claro', 'dark' => 'Oscuro', 'auto' => 'Automático']
```

### Admin Panel Example

```php
// Build settings form with translations
$settings = Settings::allWithMetadata('site');

foreach ($settings as $setting) {
    $label = $setting->getTranslatedLabel(app()->getLocale());
    $description = $setting->getTranslatedDescription(app()->getLocale());

    echo "<label>{$label}</label>";
    echo "<p class='help'>{$description}</p>";
    // Render input based on $setting->input_type
}
```

## Metadata

Store additional information about settings for UI generation and documentation.

### Complete Metadata

```php
Settings::setWithMetadata(
    key: 'max_upload_size',
    value: 10,
    group: 'system',
    label: ['en' => 'Max Upload Size', 'es' => 'Tamaño Máximo de Carga'],
    description: ['en' => 'Maximum file upload size in MB'],
    validationRules: ['required', 'integer', 'min:1', 'max:100'],
    options: null, // For select inputs
    inputType: 'number',
    isPublic: false,
    order: 10,
    encrypted: false,
    viewPermissionType: 'authenticated',
    viewPermissions: [],
    editPermissionType: 'roles',
    editPermissions: ['admin'],
);
```

### Input Types

Specify how the setting should be rendered in forms:

```php
// Text input
Settings::setWithMetadata(key: 'site.name', value: 'My App', inputType: 'text');

// Textarea
Settings::setWithMetadata(key: 'site.description', value: '...', inputType: 'textarea');

// Number
Settings::setWithMetadata(key: 'max_users', value: 100, inputType: 'number');

// Email
Settings::setWithMetadata(key: 'contact.email', value: 'a@b.com', inputType: 'email');

// URL
Settings::setWithMetadata(key: 'site.url', value: 'https://...', inputType: 'url');

// Select
Settings::setWithMetadata(
    key: 'theme',
    value: 'dark',
    inputType: 'select',
    options: ['light' => ['en' => 'Light'], 'dark' => ['en' => 'Dark']]
);

// Checkbox
Settings::setWithMetadata(key: 'maintenance', value: false, inputType: 'checkbox');

// Radio
Settings::setWithMetadata(
    key: 'layout',
    value: 'grid',
    inputType: 'radio',
    options: ['list' => ['en' => 'List'], 'grid' => ['en' => 'Grid']]
);
```

### Ordering

Control display order in admin panels:

```php
Settings::setWithMetadata(key: 'site.name', value: 'My App', order: 1);
Settings::setWithMetadata(key: 'site.logo', value: '/logo.png', order: 2);
Settings::setWithMetadata(key: 'site.description', value: '...', order: 3);

// Query ordered settings
$settings = Setting::ordered()->get();
```

### Retrieving with Metadata

```php
// Get all settings with full metadata
$settings = Settings::allWithMetadata('site');

foreach ($settings as $setting) {
    echo "Key: {$setting->key}\n";
    echo "Value: {$setting->getCastedValue()}\n";
    echo "Type: {$setting->type}\n";
    echo "Input: {$setting->input_type}\n";
    echo "Label: " . $setting->getTranslatedLabel() . "\n";
    echo "Order: {$setting->order}\n";
}
```

## What's Next?

- **[Permissions](permissions.md)** - Control who can view and edit settings
- **[Audit & History](audit-history.md)** - Track changes and rollback
- **[Import & Export](import-export.md)** - Backup and migration
- **[API Reference](api-reference.md)** - Complete method documentation

---

[← User Settings](user-settings.md) | [Permissions →](permissions.md)
