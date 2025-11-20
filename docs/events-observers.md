# Events & Observers

React to setting changes with Laravel's event system. Laravel Settings dispatches events for all create, update, and delete operations.

## Table of Contents

- [Available Events](#available-events)
- [Listening to Events](#listening-to-events)
- [Observers](#observers)
- [Use Cases](#use-cases)

## Available Events

### Setting Events

- `Stratos\Settings\Events\SettingCreated` - Fired when a new setting is created
- `Stratos\Settings\Events\SettingUpdated` - Fired when a setting is updated
- `Stratos\Settings\Events\SettingDeleted` - Fired when a setting is deleted

### User Setting Events

- `Stratos\Settings\Events\UserSettingCreated` - Fired when a user setting is created
- `Stratos\Settings\Events\UserSettingUpdated` - Fired when a user setting is updated
- `Stratos\Settings\Events\UserSettingDeleted` - Fired when a user setting is deleted

### Event Properties

Each event contains the setting model:

```php
class SettingUpdated
{
    public function __construct(public Setting $setting)
    {
    }
}
```

## Listening to Events

### Creating a Listener

```bash
php artisan make:listener NotifyAdminOnSettingChange
```

```php
namespace App\Listeners;

use Stratos\Settings\Events\SettingUpdated;
use Illuminate\Support\Facades\Notification;
use App\Notifications\SettingChangedNotification;

class NotifyAdminOnSettingChange
{
    public function handle(SettingUpdated $event): void
    {
        $setting = $event->setting;

        // Notify admins of critical setting changes
        if (in_array($setting->key, ['api.stripe_key', 'system.maintenance'])) {
            Notification::send(
                User::role('admin')->get(),
                new SettingChangedNotification($setting)
            );
        }
    }
}
```

### Registering Listeners

In `app/Providers/EventServiceProvider.php`:

```php
use Stratos\Settings\Events\SettingUpdated;
use App\Listeners\NotifyAdminOnSettingChange;

protected $listen = [
    SettingUpdated::class => [
        NotifyAdminOnSettingChange::class,
    ],
];
```

### Closure Listeners

```php
use Illuminate\Support\Facades\Event;
use Stratos\Settings\Events\SettingUpdated;

Event::listen(SettingUpdated::class, function (SettingUpdated $event) {
    Log::info('Setting updated', [
        'key' => $event->setting->key,
        'value' => $event->setting->getCastedValue(),
    ]);
});
```

## Observers

Laravel Settings uses observers to automatically handle caching and history tracking.

### SettingObserver

The `SettingObserver` automatically:

- Clears cache when settings are created, updated, or deleted
- Records history entries for audit trail
- Dispatches events

### UserSettingObserver

The `UserSettingObserver` handles the same for user settings.

### Observer Behavior

```php
// When you set a setting
Settings::set('site.name', 'My App');

// The observer automatically:
// 1. Clears cache for this setting
// 2. Creates history entry
// 3. Dispatches SettingUpdated event
```

## Use Cases

### Clear Application Cache on Setting Change

```php
use Stratos\Settings\Events\SettingUpdated;

Event::listen(SettingUpdated::class, function (SettingUpdated $event) {
    if ($event->setting->group === 'cache') {
        Cache::flush();
    }
});
```

### Log All Setting Changes

```php
Event::listen([
    SettingCreated::class,
    SettingUpdated::class,
    SettingDeleted::class,
], function ($event) {
    Log::channel('audit')->info('Setting modified', [
        'action' => class_basename($event),
        'key' => $event->setting->key,
        'user' => auth()->id(),
    ]);
});
```

### Invalidate Page Cache

```php
use Stratos\Settings\Events\SettingUpdated;

Event::listen(SettingUpdated::class, function (SettingUpdated $event) {
    if (str_starts_with($event->setting->key, 'site.')) {
        // Clear page cache when site settings change
        Artisan::call('page-cache:clear');
    }
});
```

### Send Webhooks

```php
Event::listen(SettingUpdated::class, function (SettingUpdated $event) {
    if ($event->setting->key === 'api.webhook_url') {
        Http::post($event->setting->getCastedValue(), [
            'event' => 'webhook_url_updated',
            'value' => $event->setting->getCastedValue(),
        ]);
    }
});
```

### Sync with External Service

```php
Event::listen(SettingUpdated::class, function (SettingUpdated $event) {
    if ($event->setting->group === 'sync') {
        dispatch(new SyncSettingToExternalService($event->setting));
    }
});
```

---

[← Import & Export](import-export.md) | [Artisan Commands →](artisan-commands.md)
