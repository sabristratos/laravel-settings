# Testing

Guide to testing your application's use of Laravel Settings.

## Table of Contents

- [Unit Tests](#unit-tests)
- [Feature Tests](#feature-tests)
- [Mocking Settings](#mocking-settings)

## Unit Tests

### Testing Setting Creation

```php
use Stratos\Settings\Facades\Settings;
use Stratos\Settings\Models\Setting;

test('can create a setting', function () {
    Settings::set('test.key', 'test value');

    expect(Settings::get('test.key'))->toBe('test value');
    expect(Settings::has('test.key'))->toBeTrue();
});

test('setting is stored in database', function () {
    Settings::set('site.name', 'My App');

    $this->assertDatabaseHas('settings', [
        'key' => 'site.name',
        'value' => 'My App',
    ]);
});
```

### Testing Type Casting

```php
test('casts integer values correctly', function () {
    Settings::set('max_users', 100);

    $setting = Setting::where('key', 'max_users')->first();

    expect($setting->type)->toBe('int');
    expect($setting->getCastedValue())->toBe(100);
    expect($setting->getCastedValue())->toBeInt();
});

test('casts boolean values correctly', function () {
    Settings::set('maintenance_mode', true);

    expect(Settings::get('maintenance_mode'))->toBeTrue();
    expect(Settings::get('maintenance_mode'))->toBeBool();
});
```

### Testing Encryption

```php
test('encrypts sensitive values', function () {
    Settings::setEncrypted('api.key', 'secret123');

    $setting = Setting::where('key', 'api.key')->first();

    expect($setting->encrypted)->toBeTrue();
    expect($setting->value)->not->toBe('secret123'); // Value is encrypted
    expect(Settings::get('api.key'))->toBe('secret123'); // Decrypted when retrieved
});
```

### Testing Validation

```php
use Illuminate\Validation\ValidationException;

test('validates setting values', function () {
    Settings::setWithMetadata(
        key: 'max_users',
        value: 100,
        validationRules: ['integer', 'min:1', 'max:1000']
    );

    $setting = Setting::where('key', 'max_users')->first();

    expect($setting->validate(500))->toBeTrue();
    expect($setting->validate(5000))->toBeFalse();
});
```

## Feature Tests

### Testing User Settings

```php
use App\Models\User;

test('user can update their preferences', function () {
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

### Testing Permissions

```php
test('only admins can edit sensitive settings', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $user = User::factory()->create();

    Settings::setPermissions(
        key: 'api.key',
        viewType: 'roles',
        viewPermissions: ['admin'],
        editType: 'roles',
        editPermissions: ['admin']
    );

    $setting = Setting::where('key', 'api.key')->first();

    expect($setting->canEdit($admin))->toBeTrue();
    expect($setting->canEdit($user))->toBeFalse();
});
```

### Testing History

```php
test('creates history on setting update', function () {
    Settings::set('site.name', 'Original Name');
    Settings::set('site.name', 'New Name');

    $history = Settings::getHistory('site.name');

    expect($history)->toHaveCount(2); // created + updated
    expect($history->first()->old_value)->toBe('Original Name');
    expect($history->first()->new_value)->toBe('New Name');
});

test('can rollback to previous version', function () {
    Settings::set('site.name', 'v1');
    Settings::set('site.name', 'v2');

    $history = Settings::getHistory('site.name');
    Settings::restoreToVersion('site.name', $history->first()->id);

    expect(Settings::get('site.name'))->toBe('v1');
});
```

### Testing Events

```php
use Stratos\Settings\Events\SettingUpdated;
use Illuminate\Support\Facades\Event;

test('dispatches event on setting update', function () {
    Event::fake([SettingUpdated::class]);

    Settings::set('site.name', 'My App');

    Event::assertDispatched(SettingUpdated::class);
});
```

## Mocking Settings

### Mock Settings in Tests

```php
use Stratos\Settings\Facades\Settings;

test('feature works when enabled', function () {
    Settings::shouldReceive('get')
        ->with('features.new_dashboard')
        ->andReturn(true);

    $response = $this->get('/dashboard');

    $response->assertViewIs('dashboard.v2');
});

test('feature is hidden when disabled', function () {
    Settings::shouldReceive('get')
        ->with('features.new_dashboard', false)
        ->andReturn(false);

    $response = $this->get('/dashboard');

    $response->assertViewIs('dashboard.v1');
});
```

### Testing with Real Database

```php
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('settings persist in database', function () {
    Settings::set('test.key', 'value');

    $this->assertDatabaseHas('settings', [
        'key' => 'test.key',
        'value' => 'value',
    ]);

    expect(Settings::get('test.key'))->toBe('value');
});
```

### Testing Cache Behavior

```php
use Illuminate\Support\Facades\Cache;

test('settings are cached', function () {
    Cache::spy();

    Settings::set('site.name', 'My App');
    Settings::get('site.name');

    Cache::shouldHaveReceived('remember');
});

test('cache is cleared on update', function () {
    Cache::spy();

    Settings::set('site.name', 'My App');
    Settings::set('site.name', 'New Name');

    Cache::shouldHaveReceived('forget');
});
```

---

[← Recipes](recipes.md) | [Migration Guides →](migration-guides.md)
