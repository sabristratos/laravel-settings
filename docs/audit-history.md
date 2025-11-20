# Audit Trail & History

Track all changes to your settings with complete audit trails, including who made changes, when, and what was changed. Roll back to previous versions when needed.

## Table of Contents

- [Overview](#overview)
- [Viewing History](#viewing-history)
- [Rolling Back Changes](#rolling-back-changes)
- [History Details](#history-details)
- [Configuration](#configuration)
- [Use Cases](#use-cases)

## Overview

Laravel Settings automatically tracks all changes to settings and user settings, creating a complete audit trail.

### What's Tracked

For every setting change, the following is recorded:

- **Old value** and **new value**
- **Old type** and **new type**
- **User** who made the change
- **IP address** of the request
- **User agent** (browser/client)
- **Action** (created, updated, deleted)
- **Timestamp**

### Automatic Tracking

History is tracked automatically via observers - no manual intervention required:

```php
use Stratos\Settings\Facades\Settings;

// This automatically creates a history entry
Settings::set('site.name', 'My Application');
// ✓ Recorded: created, value="My Application", user_id=1, ip=127.0.0.1

// This also creates a history entry
Settings::set('site.name', 'New Name');
// ✓ Recorded: updated, old="My Application", new="New Name", user_id=1

// Even deletions are tracked
Settings::forget('site.name');
// ✓ Recorded: deleted, old="New Name", new=null, user_id=1
```

## Viewing History

### Get History for a Setting

```php
// Get last 50 changes (default)
$history = Settings::getHistory('site.name');

foreach ($history as $change) {
    echo "Changed from '{$change->old_value}' to '{$change->new_value}'";
    echo " by User #{$change->user_id}";
    echo " on {$change->created_at}";
    echo " from IP {$change->ip_address}\n";
}
```

### Get History with Limit

```php
// Get last 10 changes
$history = Settings::getHistory('site.name', 10);

// Get last 100 changes
$history = Settings::getHistory('site.name', 100);
```

### Get All History

Get history for all settings:

```php
// Get last 100 changes across all settings (default)
$allHistory = Settings::getAllHistory();

// Get last 500 changes
$allHistory = Settings::getAllHistory(500);

foreach ($allHistory as $change) {
    echo "{$change->setting_key}: {$change->action} ";
    echo "on {$change->created_at}\n";
}
```

### User Setting History

Track changes to user-specific settings:

```php
// Get history for user setting
$history = Settings::user()->getHistory('theme');

// Get all history for current user's settings
$allHistory = Settings::user()->getAllHistory();
```

### Query History Directly

```php
use Stratos\Settings\Models\SettingHistory;

// Get history for specific setting
$history = SettingHistory::where('setting_key', 'site.name')
    ->orderBy('created_at', 'desc')
    ->get();

// Get history by user
$userChanges = SettingHistory::where('user_id', 1)
    ->orderBy('created_at', 'desc')
    ->get();

// Get recent changes (last 24 hours)
$recentChanges = SettingHistory::where('created_at', '>=', now()->subDay())
    ->orderBy('created_at', 'desc')
    ->get();

// Get changes by IP
$ipChanges = SettingHistory::where('ip_address', '192.168.1.100')
    ->get();

// Get only updates (exclude creates and deletes)
$updates = SettingHistory::where('action', 'updated')
    ->orderBy('created_at', 'desc')
    ->get();
```

## Rolling Back Changes

### Restore to Previous Version

```php
// Get history
$history = Settings::getHistory('site.name');

// Find the version you want to restore
$previousVersion = $history->first(); // Most recent change

// Restore to that version
Settings::restoreToVersion('site.name', $previousVersion->id);

// The setting value is now restored
$value = Settings::get('site.name'); // Returns the old value
```

### Restore User Setting

```php
// Get user setting history
$history = Settings::user()->getHistory('theme');

// Restore to previous version
Settings::user()->restoreToVersion('theme', $history->first()->id);
```

### Rollback Example

```php
// A user accidentally changes a critical setting
Settings::set('api.rate_limit', 10); // Oops, too low!

// Check history
$history = Settings::getHistory('api.rate_limit');
// [
//     0 => old: 1000, new: 10, created_at: 2024-01-15 14:30:00
//     1 => old: 500, new: 1000, created_at: 2024-01-10 09:00:00
// ]

// Restore to the previous value (1000)
Settings::restoreToVersion('api.rate_limit', $history[0]->id);

// Verify
Settings::get('api.rate_limit'); // Returns: 1000
```

## History Details

### SettingHistory Model

The `SettingHistory` model contains these fields:

| Field | Type | Description |
|-------|------|-------------|
| `id` | int | Primary key |
| `setting_key` | string | The setting key that was changed |
| `old_value` | text | Previous value (null for creates) |
| `new_value` | text | New value (null for deletes) |
| `old_type` | string | Previous data type |
| `new_type` | string | New data type |
| `user_id` | int | User who made the change |
| `ip_address` | string | IP address of the request |
| `user_agent` | text | Browser/client information |
| `action` | string | 'created', 'updated', or 'deleted' |
| `created_at` | timestamp | When the change occurred |
| `updated_at` | timestamp | Record update time |

### Action Types

```php
// Created
$history->action === 'created'; // New setting created

// Updated
$history->action === 'updated'; // Existing setting modified

// Deleted
$history->action === 'deleted'; // Setting removed
```

### Accessing User Information

```php
$history = Settings::getHistory('site.name')->first();

// Get the user who made the change
$user = $history->user;
echo "Changed by: {$user->name}";

// Check if change was made by specific user
if ($history->user_id === auth()->id()) {
    echo "You made this change";
}
```

## Configuration

Configure audit trail behavior in `config/settings.php`:

```php
'audit' => [
    // Enable/disable history tracking
    'enabled' => env('SETTINGS_AUDIT_ENABLED', true),

    // Track IP addresses
    'track_ip' => env('SETTINGS_AUDIT_TRACK_IP', true),

    // Track user agent strings
    'track_user_agent' => env('SETTINGS_AUDIT_TRACK_USER_AGENT', true),

    // Automatically prune old history (optional)
    'prune_after_days' => env('SETTINGS_AUDIT_PRUNE_DAYS', 365),
],
```

### Disable Audit Trail

```env
# In .env file
SETTINGS_AUDIT_ENABLED=false
```

Or in config:

```php
'audit' => [
    'enabled' => false,
],
```

### Disable IP Tracking (GDPR Compliance)

```env
SETTINGS_AUDIT_TRACK_IP=false
SETTINGS_AUDIT_TRACK_USER_AGENT=false
```

## Use Cases

### Compliance and Accountability

Track who changed critical settings:

```php
// View all changes to sensitive settings
$apiKeyChanges = SettingHistory::where('setting_key', 'api.stripe_key')
    ->with('user')
    ->orderBy('created_at', 'desc')
    ->get();

foreach ($apiKeyChanges as $change) {
    Log::info("API key modified", [
        'user' => $change->user->email,
        'ip' => $change->ip_address,
        'time' => $change->created_at,
        'action' => $change->action,
    ]);
}
```

### Debugging Configuration Issues

Investigate when a setting was changed:

```php
// When did the rate limit change?
$rateLimitHistory = Settings::getHistory('api.rate_limit');

foreach ($rateLimitHistory as $change) {
    echo "{$change->created_at}: Changed from {$change->old_value} to {$change->new_value}";
    echo " by {$change->user->name}\n";
}
```

### Audit Dashboard

Create an admin dashboard showing recent changes:

```php
// Controller
public function auditLog()
{
    $recentChanges = SettingHistory::with('user')
        ->orderBy('created_at', 'desc')
        ->paginate(50);

    return view('admin.audit', compact('recentChanges'));
}

// View
@foreach($recentChanges as $change)
    <tr>
        <td>{{ $change->setting_key }}</td>
        <td>{{ $change->action }}</td>
        <td>{{ $change->old_value }}</td>
        <td>{{ $change->new_value }}</td>
        <td>{{ $change->user->name ?? 'System' }}</td>
        <td>{{ $change->ip_address }}</td>
        <td>{{ $change->created_at->diffForHumans() }}</td>
    </tr>
@endforeach
```

### Compare Versions

Show differences between versions:

```php
$history = Settings::getHistory('config.json');

foreach ($history as $index => $change) {
    if ($index === 0) continue; // Skip first

    $previous = $history[$index];
    echo "Change at {$change->created_at}:\n";
    echo "  From: " . json_encode($previous->old_value, JSON_PRETTY_PRINT) . "\n";
    echo "  To: " . json_encode($change->new_value, JSON_PRETTY_PRINT) . "\n";
}
```

### Rollback After Incident

Quickly restore settings after an incident:

```php
// Get all changes in the last hour
$recentChanges = SettingHistory::where('created_at', '>=', now()->subHour())
    ->orderBy('created_at', 'desc')
    ->get();

// Review and rollback problematic changes
foreach ($recentChanges as $change) {
    if ($change->user_id === $suspiciousUserId) {
        echo "Rolling back: {$change->setting_key}\n";
        Settings::restoreToVersion($change->setting_key, $change->id);
    }
}
```

### Export Audit Log

```php
// Export to CSV
$history = SettingHistory::with('user')
    ->orderBy('created_at', 'desc')
    ->get();

$csv = fopen('audit-log.csv', 'w');
fputcsv($csv, ['Key', 'Action', 'Old Value', 'New Value', 'User', 'IP', 'Date']);

foreach ($history as $change) {
    fputcsv($csv, [
        $change->setting_key,
        $change->action,
        $change->old_value,
        $change->new_value,
        $change->user->name ?? 'System',
        $change->ip_address,
        $change->created_at,
    ]);
}

fclose($csv);
```

## Best Practices

### 1. Regular Reviews

Periodically review audit logs for anomalies:

```php
// Weekly audit review
$weeklyChanges = SettingHistory::where('created_at', '>=', now()->subWeek())
    ->with('user')
    ->get();

// Alert on suspicious activity
$suspiciousChanges = $weeklyChanges->filter(function ($change) {
    return $change->action === 'deleted' &&
           $change->setting_key === 'api.stripe_key';
});

if ($suspiciousChanges->count() > 0) {
    // Send alert to administrators
}
```

### 2. Archive Old History

Keep database size manageable by archiving old history:

```php
// Archive history older than 1 year
$oldHistory = SettingHistory::where('created_at', '<', now()->subYear())->get();

// Export to file or external storage
Storage::put('audit-archive.json', $oldHistory->toJson());

// Delete archived records
SettingHistory::where('created_at', '<', now()->subYear())->delete();
```

### 3. Monitor Critical Settings

Set up alerts for changes to critical settings:

```php
// app/Observers/SettingObserver.php
public function updated(Setting $setting)
{
    $criticalKeys = ['api.stripe_key', 'api.aws_secret', 'system.maintenance'];

    if (in_array($setting->key, $criticalKeys)) {
        // Send notification to admins
        Notification::send(
            User::role('admin')->get(),
            new CriticalSettingChanged($setting)
        );
    }
}
```

---

[← Permissions](permissions.md) | [Import & Export →](import-export.md)
