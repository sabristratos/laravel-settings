# Laravel Settings

[![Tests](https://img.shields.io/badge/tests-131%20passing-success)](tests)
[![PHP Version](https://img.shields.io/badge/php-%5E8.3-blue)](https://php.net)
[![Laravel Version](https://img.shields.io/badge/laravel-%5E12.0-red)](https://laravel.com)
[![License](https://img.shields.io/badge/license-MIT-green)](LICENSE)
[![Documentation](https://img.shields.io/badge/docs-live-brightgreen)](https://sabristratos.github.io/laravel-settings)

A flexible, enterprise-ready settings management package for Laravel applications. Manage global settings, user preferences, and dynamic configuration with powerful features including encryption, validation, audit trails, and granular permissions.

## ✨ Key Features

- 🌍 **Global & User Settings** - Application-wide and per-user preferences
- 🔒 **Encryption** - Secure sensitive data with Laravel's encryption
- ✅ **Validation** - Per-setting validation rules
- 📝 **Audit Trail** - Complete change history with rollback
- 🔐 **Permissions** - Role and permission-based access control
- 🌐 **Multilingual** - Translations for labels and descriptions
- ⚡ **Caching** - Automatic caching with smart invalidation
- 📦 **Import/Export** - Backup and migration (JSON/YAML)
- 🎯 **Type Casting** - Automatic type detection (string, int, bool, array, json)
- 🧪 **Well Tested** - 131 Pest tests with full coverage

## 📋 Requirements

- PHP 8.3 or higher
- Laravel 12.0 or higher

## 🚀 Quick Start

### Installation

```bash
composer require stratos/laravel-settings
php artisan vendor:publish --tag="settings-migrations"
php artisan migrate
```

### Basic Usage

```php
use Stratos\Settings\Facades\Settings;

// Set a value
Settings::set('site.name', 'My Application');

// Get a value
$name = Settings::get('site.name', 'Default Name');

// Use helper
$name = setting('site.name');

// Blade directive
<h1>@setting('site.name')</h1>
```

### User Settings

```php
// Set user preference
Settings::user()->set('theme', 'dark');

// Get user preference
$theme = user_setting('theme', 'light');

// Blade
<body class="theme-@userSetting('theme')">
```

### Advanced Features

```php
// Encrypted values
Settings::setEncrypted('api.stripe_key', 'sk_live_...');

// With validation
Settings::setWithMetadata(
    key: 'max_users',
    value: 100,
    validationRules: ['integer', 'min:1', 'max:1000']
);

// With permissions
Settings::setPermissions(
    key: 'api.credentials',
    viewType: 'roles',
    viewPermissions: ['admin', 'developer'],
    editType: 'roles',
    editPermissions: ['admin']
);

// View history
$history = Settings::getHistory('site.name');

// Rollback
Settings::restoreToVersion('site.name', $historyId);
```

### Artisan Commands

```bash
# Create setting interactively
php artisan settings:create

# Set a setting
php artisan settings:set site.name "My App"

# Get a setting
php artisan settings:get site.name

# List all settings
php artisan settings:list

# Export/Import
php artisan settings:export settings.json
php artisan settings:import settings.json
```

## 📚 Documentation

For complete documentation, visit the [docs](docs) folder:

### Getting Started
- **[Installation](docs/installation.md)** - Detailed setup and configuration
- **[Basic Usage](docs/usage.md)** - CRUD operations and helpers
- **[User Settings](docs/user-settings.md)** - Per-user preferences

### Features
- **[Advanced Features](docs/advanced-features.md)** - Validation, encryption, types, caching
- **[Permissions](docs/permissions.md)** - Access control system
- **[Audit & History](docs/audit-history.md)** - Change tracking and rollback
- **[Import & Export](docs/import-export.md)** - Backup and migration
- **[Events & Observers](docs/events-observers.md)** - Event system

### Reference
- **[API Reference](docs/api-reference.md)** - Complete API documentation
- **[Artisan Commands](docs/artisan-commands.md)** - CLI reference
- **[REST API](docs/rest-api.md)** - HTTP endpoints
- **[Database Schema](docs/database-schema.md)** - Table structure

### Guides
- **[Recipes](docs/recipes.md)** - Practical examples
- **[Testing](docs/testing.md)** - Testing guide
- **[Migration Guides](docs/migration-guides.md)** - Migrate from other packages
- **[Troubleshooting](docs/troubleshooting.md)** - Common issues

## 🎯 Use Cases

Perfect for:

- **SaaS Applications** - Per-tenant or per-user configuration
- **API Credentials** - Securely store API keys with encryption
- **Feature Flags** - Toggle features dynamically
- **User Preferences** - Theme, language, timezone, notifications
- **Email Configuration** - Dynamic SMTP settings
- **Multi-Tenant Apps** - Tenant-specific settings
- **Enterprise Apps** - Audit compliance and change tracking

## 🆚 Comparison

| Feature | Laravel Settings | Spatie Settings | Config Files |
|---------|-----------------|-----------------|--------------|
| Global Settings | ✅ | ✅ | ✅ |
| User Settings | ✅ | ❌ | ❌ |
| Encryption | ✅ | ❌ | ❌ |
| Validation | ✅ | ❌ | ❌ |
| Permissions | ✅ | ❌ | ❌ |
| Audit Trail | ✅ | ❌ | ❌ |
| Import/Export | ✅ | ❌ | ❌ |
| History/Rollback | ✅ | ❌ | ❌ |
| Caching | ✅ | ✅ | ✅ |
| Dynamic Updates | ✅ | ✅ | ❌ |

## 🧪 Testing

```bash
# Run tests
vendor/bin/pest

# Run with coverage
vendor/bin/pest --coverage
```

## 📝 Changelog

See [CHANGELOG.md](CHANGELOG.md) for recent changes.

## 🤝 Contributing

Contributions are welcome! Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

## 🔒 Security

If you discover any security-related issues, please email security@stratosdigital.com instead of using the issue tracker.

## 📄 License

Laravel Settings is open-sourced software licensed under the [MIT license](LICENSE).

## 👨‍💻 Credits

- **Author**: Mohamed Sabri Ben Chaabane
- **Company**: [Stratos Digital](https://stratosdigital.com)
- **Contributors**: [All Contributors](../../contributors)

## 🌟 Support

If you find this package useful, please consider:

- ⭐ Starring the repository
- 🐛 [Reporting issues](https://github.com/sabristratos/laravel-settings/issues)
- 💡 [Suggesting features](https://github.com/sabristratos/laravel-settings/issues/new)
- 📖 [Improving documentation](docs)

---

**[📚 Read the Full Documentation](docs/index.md)** | **[🚀 View on GitHub](https://github.com/sabristratos/laravel-settings)**
