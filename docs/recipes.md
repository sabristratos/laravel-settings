# Recipes

Practical, real-world examples for common use cases.

## API Credentials

Store API keys and secrets securely.

```php
// Stripe API
Settings::setWithMetadata(
    key: 'api.stripe.secret_key',
    value: env('STRIPE_SECRET'),
    encrypted: true,
    group: 'api',
    label: ['en' => 'Stripe Secret Key'],
    description: ['en' => 'Stripe API secret key for payments'],
    viewPermissionType: 'roles',
    viewPermissions: ['admin', 'developer'],
    editPermissionType: 'roles',
    editPermissions: ['admin']
);

// AWS Credentials
Settings::setBulk([
    'api.aws.access_key' => env('AWS_ACCESS_KEY_ID'),
    'api.aws.secret_key' => env('AWS_SECRET_ACCESS_KEY'),
    'api.aws.region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
]);

Settings::setEncrypted('api.aws.secret_key', env('AWS_SECRET_ACCESS_KEY'));

// Usage
$stripeKey = Settings::encrypted('api.stripe.secret_key');
Stripe::setApiKey($stripeKey);
```

## User Theme Preferences

Implement dark mode and theme switching.

```php
// Store user theme preference
public function updateTheme(Request $request)
{
    $validated = $request->validate([
        'theme' => 'required|in:light,dark,auto',
    ]);

    Settings::user()->set('theme', $validated['theme']);

    return back()->with('success', 'Theme updated');
}

// Apply theme in middleware
public function handle($request, Closure $next)
{
    if (auth()->check()) {
        $theme = user_setting('theme', 'light');
        view()->share('userTheme', $theme);
    }

    return $next($request);
}

// In Blade layout
<body class="theme-{{ $userTheme ?? 'light' }}">
```

## Feature Flags

Toggle features on/off dynamically.

```php
// Set feature flags
Settings::setBulk([
    'features.new_dashboard' => false,
    'features.beta_api' => true,
    'features.experimental_ui' => false,
]);

// Check in code
if (Settings::get('features.new_dashboard')) {
    return view('dashboard.v2');
}
return view('dashboard.v1');

// Middleware
public function handle($request, Closure $next)
{
    if (!Settings::get('features.beta_api', false)) {
        abort(404);
    }

    return $next($request);
}

// Blade directive
@if(setting('features.experimental_ui'))
    @include('partials.new-ui')
@else
    @include('partials.old-ui')
@endif
```

## Maintenance Mode

Dynamic maintenance mode with custom messages.

```php
// Enable maintenance
Settings::set('site.maintenance_mode', true);
Settings::set('site.maintenance_message', 'We\'ll be back soon!');

// Middleware
public function handle($request, Closure $next)
{
    if (Settings::get('site.maintenance_mode', false)) {
        $message = Settings::get('site.maintenance_message', 'Under maintenance');

        if (!auth()->check() || !auth()->user()->hasRole('admin')) {
            return response()->view('maintenance', ['message' => $message], 503);
        }
    }

    return $next($request);
}
```

## Email Configuration

Dynamic SMTP settings.

```php
// Store email configuration
Settings::setBulk([
    'email.driver' => 'smtp',
    'email.host' => 'smtp.mailtrap.io',
    'email.port' => 587,
    'email.username' => 'username',
    'email.password' => 'password',
    'email.encryption' => 'tls',
    'email.from.address' => 'noreply@example.com',
    'email.from.name' => 'My Application',
]);

// Encrypt password
Settings::setEncrypted('email.password', 'password123');

// Apply to mail config
config([
    'mail.mailers.smtp.host' => Settings::get('email.host'),
    'mail.mailers.smtp.port' => Settings::get('email.port'),
    'mail.mailers.smtp.username' => Settings::get('email.username'),
    'mail.mailers.smtp.password' => Settings::encrypted('email.password'),
    'mail.from.address' => Settings::get('email.from.address'),
    'mail.from.name' => Settings::get('email.from.name'),
]);
```

## Multi-Tenant Settings

Tenant-specific configuration.

```php
// Store tenant settings with prefix
$tenantId = auth()->user()->tenant_id;

Settings::set("tenant.{$tenantId}.company_name", 'Acme Corp');
Settings::set("tenant.{$tenantId}.logo", '/logos/acme.png');
Settings::set("tenant.{$tenantId}.primary_color", '#FF5733');

// Retrieve tenant settings
$companyName = Settings::get("tenant.{$tenantId}.company_name");

// Or use a helper
function tenant_setting($key, $default = null) {
    $tenantId = auth()->user()->tenant_id;
    return Settings::get("tenant.{$tenantId}.{$key}", $default);
}

$logo = tenant_setting('logo');
```

## Notification Preferences

User notification settings.

```php
// Store preferences
Settings::user()->setBulk([
    'notifications.email' => true,
    'notifications.sms' => false,
    'notifications.push' => true,
    'notifications.marketing' => false,
    'notifications.frequency' => 'daily', // instant, daily, weekly
]);

// Check before sending
if (user_setting('notifications.email', true)) {
    Mail::to($user)->send(new OrderShipped($order));
}

if (user_setting('notifications.push', false)) {
    $user->notify(new PushNotification($message));
}
```

## Localization Preferences

User language and timezone.

```php
// Store user locale
Settings::user()->set('language', 'es');
Settings::user()->set('timezone', 'Europe/Madrid');

// Apply in middleware
public function handle($request, Closure $next)
{
    if (auth()->check()) {
        $locale = user_setting('language', config('app.locale'));
        app()->setLocale($locale);

        $timezone = user_setting('timezone', config('app.timezone'));
        date_default_timezone_set($timezone);
    }

    return $next($request);
}
```

---

[← Database Schema](database-schema.md) | [Testing →](testing.md)
