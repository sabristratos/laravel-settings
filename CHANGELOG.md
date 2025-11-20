# Changelog

All notable changes to `stratos/laravel-settings` will be documented in this file.

## [Unreleased]

## [1.0.0] - 2025-11-20

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
- Permission system (public, authenticated, roles, permissions)
- Events & Observers for setting changes
- Optional REST API endpoints
- Interactive CLI commands using Laravel Prompts
- Helper functions and Blade directives
- Comprehensive test suite with Pest (131 tests)
- Laravel 12 ready with PHP 8.3+ support
- Complete @method annotations to Settings facade
- Route names to all API endpoints (settings.api.*)
- LICENSE file (MIT)
- CHANGELOG.md for version tracking
- Comprehensive documentation (17 pages)
- GitHub Pages documentation site with Docsify
- API reference documentation
- Testing guide
- Migration guides from other solutions
- Practical recipes and examples

### Fixed
- API Controller type mismatch when using `public=true` query parameter
- Cache TTL type safety by ensuring integer return type
- Config locale inconsistency by adding 'de' to default locales
