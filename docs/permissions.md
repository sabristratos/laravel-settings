# Permissions

Control who can view and edit settings with Laravel Settings' granular permission system.

## Table of Contents

- [Permission Types](#permission-types)
- [Setting Permissions](#setting-permissions)
- [Checking Permissions](#checking-permissions)
- [Integration with Spatie Permission](#integration-with-spatie-permission)
- [Examples](#examples)

## Permission Types

Laravel Settings supports four permission strategies:

### 1. Public

Anyone can access (no authentication required).

```php
Settings::setPermissions(
    key: 'site.name',
    viewType: 'public',
    viewPermissions: [],
    editType: 'authenticated',
    editPermissions: []
);
```

### 2. Authenticated

Any authenticated user can access.

```php
Settings::setPermissions(
    key: 'user.default_theme',
    viewType: 'authenticated',
    viewPermissions: [],
    editType: 'authenticated',
    editPermissions: []
);
```

### 3. Roles

Only users with specific roles can access.

```php
Settings::setPermissions(
    key: 'api.stripe_key',
    viewType: 'roles',
    viewPermissions: ['admin', 'developer'],
    editType: 'roles',
    editPermissions: ['admin']
);
```

### 4. Permissions

Only users with specific permissions can access.

```php
Settings::setPermissions(
    key: 'system.maintenance_mode',
    viewType: 'permissions',
    viewPermissions: ['view-settings', 'manage-system'],
    editType: 'permissions',
    editPermissions: ['edit-settings', 'manage-system']
);
```

## Setting Permissions

### Basic Permission Setup

```php
use Stratos\Settings\Facades\Settings;

Settings::setPermissions(
    key: 'api.stripe_key',
    viewType: 'roles',
    viewPermissions: ['admin', 'billing'],
    editType: 'roles',
    editPermissions: ['admin']
);
```

### During Setting Creation

```php
Settings::setWithMetadata(
    key: 'sensitive.data',
    value: 'secret',
    viewPermissionType: 'permissions',
    viewPermissions: ['view-sensitive-data'],
    editPermissionType: 'permissions',
    editPermissions: ['edit-sensitive-data'],
    encrypted: true
);
```

### Separate View and Edit Permissions

```php
// Everyone can view, only admins can edit
Settings::setPermissions(
    key: 'site.announcement',
    viewType: 'public',
    viewPermissions: [],
    editType: 'roles',
    editPermissions: ['admin']
);

// Developers can view, only admins can edit
Settings::setPermissions(
    key: 'api.rate_limit',
    viewType: 'roles',
    viewPermissions: ['admin', 'developer'],
    editType: 'roles',
    editPermissions: ['admin']
);
```

## Checking Permissions

### Check if User Can View

```php
use Stratos\Settings\Models\Setting;

$setting = Setting::where('key', 'api.stripe_key')->first();

// Check for current user
if ($setting->canView(auth()->user())) {
    $value = $setting->getCastedValue();
}

// Check for specific user
if ($setting->canView($user)) {
    // User can view this setting
}
```

### Check if User Can Edit

```php
$setting = Setting::where('key', 'api.stripe_key')->first();

if ($setting->canEdit(auth()->user())) {
    // User can edit this setting
    $setting->setCastedValue('new-value');
    $setting->save();
}
```

### In Controllers

```php
public function show($key)
{
    $setting = Setting::where('key', $key)->firstOrFail();

    if (!$setting->canView(auth()->user())) {
        abort(403, 'Unauthorized to view this setting');
    }

    return view('settings.show', compact('setting'));
}

public function update(Request $request, $key)
{
    $setting = Setting::where('key', $key)->firstOrFail();

    if (!$setting->canEdit(auth()->user())) {
        abort(403, 'Unauthorized to edit this setting');
    }

    $setting->setCastedValue($request->value);
    $setting->save();

    return back()->with('success', 'Setting updated');
}
```

### Scoped Queries

```php
// Get only settings the user can view
$settings = Setting::all()->filter(function ($setting) {
    return $setting->canView(auth()->user());
});

// Get only settings the user can edit
$editableSettings = Setting::all()->filter(function ($setting) {
    return $setting->canEdit(auth()->user());
});
```

## Integration with Spatie Permission

Laravel Settings integrates seamlessly with [Spatie Laravel Permission](https://spatie.be/docs/laravel-permission).

### Configuration

Enable Spatie Permission integration in `config/settings.php`:

```php
'permissions' => [
    'use_spatie_permission' => true,
    'admin_role' => 'admin',
],
```

### Role-Based Permissions

```php
// Require 'admin' or 'manager' role
Settings::setPermissions(
    key: 'company.budget',
    viewType: 'roles',
    viewPermissions: ['admin', 'manager', 'accountant'],
    editType: 'roles',
    editPermissions: ['admin', 'manager']
);

// User must have one of these roles
$user = User::find(1);
$user->assignRole('manager');

$setting = Setting::where('key', 'company.budget')->first();
$setting->canView($user); // true (has 'manager' role)
$setting->canEdit($user); // true (has 'manager' role)
```

### Permission-Based Access

```php
// Require specific permissions
Settings::setPermissions(
    key: 'api.credentials',
    viewType: 'permissions',
    viewPermissions: ['view-api-credentials'],
    editType: 'permissions',
    editPermissions: ['edit-api-credentials', 'manage-api']
);

// User must have one of these permissions
$user->givePermissionTo('view-api-credentials');

$setting = Setting::where('key', 'api.credentials')->first();
$setting->canView($user); // true (has permission)
$setting->canEdit($user); // false (lacks edit permission)
```

### Admin Override

Users with the configured admin role bypass all permission checks:

```php
// config/settings.php
'permissions' => [
    'admin_role' => 'super-admin',
],

// Super admins can view/edit everything
$admin = User::find(1);
$admin->assignRole('super-admin');

$setting->canView($admin); // true (is super-admin)
$setting->canEdit($admin); // true (is super-admin)
```

## Examples

### API Credentials

```php
// Only admins and developers can view, only admins can edit
Settings::setWithMetadata(
    key: 'api.stripe_secret',
    value: 'sk_live_...',
    encrypted: true,
    viewPermissionType: 'roles',
    viewPermissions: ['admin', 'developer'],
    editPermissionType: 'roles',
    editPermissions: ['admin']
);
```

### Public Settings

```php
// Everyone can view, only admins can edit
Settings::setWithMetadata(
    key: 'site.name',
    value: 'My Application',
    isPublic: true,
    viewPermissionType: 'public',
    viewPermissions: [],
    editPermissionType: 'roles',
    editPermissions: ['admin']
);
```

### Department-Specific Settings

```php
// Only HR department can view/edit
Settings::setWithMetadata(
    key: 'hr.salary_budget',
    value: 500000,
    viewPermissionType: 'roles',
    viewPermissions: ['hr-manager', 'hr-staff'],
    editPermissionType: 'roles',
    editPermissions: ['hr-manager']
);

// Only IT department can view/edit
Settings::setWithMetadata(
    key: 'it.server_credentials',
    value: 'credentials',
    encrypted: true,
    viewPermissionType: 'roles',
    viewPermissions: ['it-admin', 'devops'],
    editPermissionType: 'roles',
    editPermissions: ['it-admin']
);
```

### Feature Flags with Permissions

```php
// Managers and admins can see beta features
Settings::setWithMetadata(
    key: 'features.beta_dashboard',
    value: false,
    viewPermissionType: 'roles',
    viewPermissions: ['admin', 'manager'],
    editPermissionType: 'roles',
    editPermissions: ['admin']
);

// In controller
if (Settings::get('features.beta_dashboard') &&
    Setting::where('key', 'features.beta_dashboard')->first()->canView(auth()->user())) {
    return view('dashboard.beta');
}
```

### Middleware Example

```php
// app/Http/Middleware/CheckSettingPermission.php
public function handle($request, Closure $next, $settingKey)
{
    $setting = Setting::where('key', $settingKey)->first();

    if (!$setting || !$setting->canView(auth()->user())) {
        abort(403, 'Unauthorized');
    }

    return $next($request);
}

// routes/web.php
Route::get('/admin/api-settings', AdminController::class)
    ->middleware('setting.permission:api.stripe_key');
```

## Best Practices

### 1. Principle of Least Privilege

Grant minimal necessary permissions:

```php
// Good - specific roles
Settings::setPermissions(
    key: 'sensitive.data',
    viewType: 'roles',
    viewPermissions: ['data-analyst'],
    editType: 'roles',
    editPermissions: ['admin']
);

// Risky - too permissive
Settings::setPermissions(
    key: 'sensitive.data',
    viewType: 'authenticated', // Any logged-in user
    viewPermissions: [],
    editType: 'authenticated',
    editPermissions: []
);
```

### 2. Separate View and Edit

Different permissions for viewing vs editing:

```php
Settings::setPermissions(
    key: 'reports.financial',
    viewType: 'roles',
    viewPermissions: ['admin', 'manager', 'accountant'],
    editType: 'roles',
    editPermissions: ['admin'] // Only admin can edit
);
```

### 3. Combine with Encryption

Sensitive data should use both encryption and permissions:

```php
Settings::setWithMetadata(
    key: 'api.aws_secret',
    value: 'secret_key',
    encrypted: true,
    viewPermissionType: 'roles',
    viewPermissions: ['admin', 'devops'],
    editPermissionType: 'roles',
    editPermissions: ['admin']
);
```

### 4. Document Permission Requirements

```php
Settings::setWithMetadata(
    key: 'system.max_connections',
    value: 100,
    description: ['en' => 'Requires system-admin permission to edit'],
    viewPermissionType: 'authenticated',
    viewPermissions: [],
    editPermissionType: 'permissions',
    editPermissions: ['system-admin']
);
```

## Troubleshooting

### Permission Checks Failing

**Problem**: `canView()` or `canEdit()` always returns false

**Solutions**:
1. Verify Spatie Permission is installed and configured
2. Check user has the required roles/permissions:
   ```php
   $user->roles; // Check assigned roles
   $user->permissions; // Check assigned permissions
   ```
3. Verify `use_spatie_permission` is enabled in config
4. Check admin role configuration matches user's role

### All Users Can Access

**Problem**: Permission restrictions not working

**Solution**: Check permission type is not set to 'public':
```php
$setting = Setting::where('key', 'secret')->first();
dd($setting->view_permission_type); // Should not be 'public'
```

---

[← Advanced Features](advanced-features.md) | [Audit & History →](audit-history.md)
