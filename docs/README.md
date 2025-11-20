# Laravel Settings Documentation

Welcome to the complete documentation for the Laravel Settings package by Stratos Digital.

## Overview

Laravel Settings is a flexible, enterprise-ready settings management package for Laravel applications. It provides comprehensive features for managing application-wide settings, user-specific preferences, and dynamic configuration with built-in support for encryption, validation, audit trails, and permissions.

## Features at a Glance

- **Flexible Storage**: Both global settings and per-user preferences
- **Type Safety**: Automatic type detection and casting (string, int, bool, array, json)
- **Security**: Built-in encryption for sensitive data
- **Performance**: Automatic caching with intelligent invalidation
- **Organization**: Group settings logically
- **Audit Trail**: Complete history tracking with rollback capability
- **Permissions**: Granular access control (public, authenticated, roles, permissions)
- **Validation**: Per-setting validation rules
- **Multilingual**: Translation support for labels, descriptions, and options
- **Developer-Friendly**: Facade, helpers, Blade directives, and Artisan commands
- **Import/Export**: Backup and migration tools (JSON, YAML)
- **Events**: React to setting changes
- **REST API**: Optional HTTP endpoints
- **Well-Tested**: 131 tests using Pest

## Getting Started

New to Laravel Settings? Start here:

1. **[Installation](installation.md)** - Get up and running in 5 minutes
2. **[Basic Usage](usage.md)** - Learn the fundamentals
3. **[Recipes](recipes.md)** - See practical examples

## Documentation Sections

### Core Documentation

| Section | Description |
|---------|-------------|
| **[Installation](installation.md)** | Installation, configuration, and quickstart guide |
| **[Basic Usage](usage.md)** | CRUD operations, helpers, Blade directives |
| **[User Settings](user-settings.md)** | Managing per-user preferences and settings |

### Features

| Section | Description |
|---------|-------------|
| **[Advanced Features](advanced-features.md)** | Type casting, validation, encryption, groups, caching, multilingual |
| **[Permissions](permissions.md)** | Complete guide to the permission system |
| **[Audit & History](audit-history.md)** | History tracking, change logs, and rollback |
| **[Import & Export](import-export.md)** | Backup, migration, and bulk operations |
| **[Events & Observers](events-observers.md)** | Event system and observers |

### Reference

| Section | Description |
|---------|-------------|
| **[API Reference](api-reference.md)** | Complete API documentation for all classes and methods |
| **[Artisan Commands](artisan-commands.md)** | All CLI commands with examples |
| **[REST API](rest-api.md)** | HTTP endpoints documentation |
| **[Database Schema](database-schema.md)** | Table structure and relationships |

### Guides

| Section | Description |
|---------|-------------|
| **[Recipes](recipes.md)** | Practical examples and common use cases |
| **[Testing](testing.md)** | Unit and feature testing guide |
| **[Migration Guides](migration-guides.md)** | Migrating from other solutions |
| **[Troubleshooting](troubleshooting.md)** | Common issues and solutions |

## Quick Examples

### Global Settings

```php
use Stratos\Settings\Facades\Settings;

// Set a value
Settings::set('site.name', 'My Application');

// Get a value
$siteName = Settings::get('site.name');

// Using helper
$siteName = setting('site.name', 'Default Name');

// Using Blade
@setting('site.name')
```

### User Settings

```php
// Set user preference
Settings::user()->set('theme', 'dark');

// Get user preference
$theme = Settings::user()->get('theme', 'light');

// Using helper
$theme = user_setting('theme', 'light');

// Using Blade
@userSetting('theme')
```

### Advanced Features

```php
// With encryption
Settings::setEncrypted('api.stripe_key', 'sk_test_...');

// With validation
Settings::setWithMetadata(
    key: 'max_users',
    value: 100,
    validationRules: ['integer', 'min:1', 'max:1000']
);

// With permissions
Settings::setPermissions(
    key: 'api.stripe_key',
    viewType: 'roles',
    viewPermissions: ['admin', 'developer'],
    editType: 'roles',
    editPermissions: ['admin']
);

// View history
$history = Settings::getHistory('site.name');

// Rollback to previous version
Settings::restoreToVersion('site.name', $historyId);
```

## Use Cases

Laravel Settings is perfect for:

- **Application Configuration**: Site name, maintenance mode, feature flags
- **User Preferences**: Theme, language, notifications, dashboard layout
- **API Credentials**: Securely store API keys and secrets with encryption
- **Email Settings**: SMTP configuration, templates, sender details
- **SEO Settings**: Meta titles, descriptions, keywords
- **Social Media**: Links, API tokens, sharing settings
- **Multi-Tenant Applications**: Tenant-specific configuration
- **SaaS Applications**: Per-organization or per-user settings

## Requirements

- PHP 8.3 or higher
- Laravel 12.0 or higher
- MySQL, PostgreSQL, SQLite, or SQL Server database

## Support & Community

- **Issues**: [GitHub Issues](https://github.com/sabristratos/laravel-settings/issues)
- **Pull Requests**: [Contributing Guide](../CONTRIBUTING.md)
- **Changelog**: [CHANGELOG.md](../CHANGELOG.md)
- **License**: [MIT License](../LICENSE)

## Version

Current version: **1.0.0**

---

**Ready to get started?** Head over to the [Installation Guide](installation.md) to install the package and create your first setting in under 5 minutes!
