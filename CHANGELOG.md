# Changelog

All notable changes to `stratos/laravel-settings` will be documented in this file.

## [Unreleased]

### Fixed
- Fixed API Controller type mismatch when using `public=true` query parameter
- Fixed cache TTL type safety by ensuring integer return type
- Fixed config locale inconsistency by adding 'de' to default locales

### Added
- Added complete @method annotations to Settings facade
- Added route names to all API endpoints (settings.api.*)
- Added LICENSE file (MIT)
- Added CHANGELOG.md for version tracking

## [1.0.0] - 2025-01-XX

### Added
- Initial release
- Site/system-wide and user-specific settings
- Type casting (string, int, bool, array, json)
- Encryption support for sensitive settings
- Built-in caching with auto-invalidation
- Settings organized into logical groups
- Multilingual support for labels and descriptions
- Per-setting validation with Laravel's validation system
- Complete audit trail with rollback capability
- Bulk operations (set, get, delete multiple settings)
- Import/Export in JSON and YAML formats
- Events & Observers for setting changes
- Optional REST API endpoints
- Interactive CLI commands using Laravel Prompts
- Helper functions and Blade directives
- Comprehensive test suite with Pest
- Laravel 12 ready with PHP 8.2+ support
