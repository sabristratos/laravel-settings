<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Strata\Settings\Facades\Settings;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
});

it('can use @setting directive to display setting value', function () {
    Settings::set('site.name', 'My Application');

    $blade = "@setting('site.name')";
    $compiled = Blade::compileString($blade);

    expect($compiled)->toContain('setting(')
        ->and($compiled)->toContain('echo e(');
});

it('can use @setting directive with default value', function () {
    $blade = "@setting('non.existent', 'Default Value')";
    $compiled = Blade::compileString($blade);

    expect($compiled)->toContain('setting(')
        ->and($compiled)->toContain('non.existent');
});

it('escapes HTML in @setting directive', function () {
    Settings::set('html.content', '<script>alert("xss")</script>');

    $blade = "@setting('html.content')";
    $compiled = Blade::compileString($blade);

    expect($compiled)->toContain('echo e(');
});

it('can use @userSetting directive to display user setting', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Settings::user()->set('theme', 'dark');

    $blade = "@userSetting('theme')";
    $compiled = Blade::compileString($blade);

    expect($compiled)->toContain('user_setting(')
        ->and($compiled)->toContain('echo e(');
});

it('can use @userSetting directive with default value', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $blade = "@userSetting('non.existent', 'light')";
    $compiled = Blade::compileString($blade);

    expect($compiled)->toContain('user_setting(')
        ->and($compiled)->toContain('non.existent');
});

it('escapes HTML in @userSetting directive', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Settings::user()->set('malicious.input', '<img src=x onerror=alert(1)>');

    $blade = "@userSetting('malicious.input')";
    $compiled = Blade::compileString($blade);

    expect($compiled)->toContain('echo e(');
});

it('renders @setting directive in actual view', function () {
    Settings::set('app.tagline', 'The Best App Ever');

    $view = Blade::render('<h1>@setting("app.tagline")</h1>');

    expect($view)->toBe('<h1>The Best App Ever</h1>');
});

it('renders @userSetting directive in actual view', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Settings::user()->set('user.language', 'English');

    $view = Blade::render('<p>Language: @userSetting("user.language")</p>');

    expect($view)->toBe('<p>Language: English</p>');
});

it('handles integer values in @setting directive', function () {
    Settings::set('max.items', 100);

    $view = Blade::render('@setting("max.items")');

    expect($view)->toBe('100');
});

it('handles boolean values in @setting directive', function () {
    Settings::set('feature.enabled', true);

    $view = Blade::render('@setting("feature.enabled")');

    expect($view)->toBe('1');
});

it('can use @setting directive with grouped settings', function () {
    Settings::set('email.from', 'noreply@example.com', 'email');

    $view = Blade::render('@setting("email.from")');

    expect($view)->toBe('noreply@example.com');
});

it('renders multiple @setting directives correctly', function () {
    Settings::set('site.name', 'My App');
    Settings::set('site.version', '1.0.0');

    $view = Blade::render('<h1>@setting("site.name")</h1><p>Version: @setting("site.version")</p>');

    expect($view)->toBe('<h1>My App</h1><p>Version: 1.0.0</p>');
});

it('can use both directives in same view', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Settings::set('app.name', 'MyApp');
    Settings::user()->set('theme', 'dark');

    $view = Blade::render('<div class="@userSetting(\'theme\')">@setting("app.name")</div>');

    expect($view)->toBe('<div class="dark">MyApp</div>');
});

it('handles empty string values', function () {
    Settings::set('empty.value', '');

    $view = Blade::render('@setting("empty.value", "default")');

    expect($view)->toBe('');
});

it('properly handles quotes in setting values', function () {
    Settings::set('quoted.value', 'Value with "quotes"');

    $view = Blade::render('@setting("quoted.value")');

    expect($view)->toContain('Value with &quot;quotes&quot;');
});
