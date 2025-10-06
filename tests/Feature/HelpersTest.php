<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Stratos\Settings\Facades\Settings;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
});

it('can use setting helper to get value', function () {
    Settings::set('helper.test', 'test value');

    expect(setting('helper.test'))->toBe('test value');
});

it('can use setting helper with default value', function () {
    expect(setting('non.existent', 'default value'))->toBe('default value');
});

it('can use user_setting helper to get value', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Settings::user()->set('user.theme', 'dark');

    expect(user_setting('user.theme'))->toBe('dark');
});

it('can use user_setting helper with default value', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    expect(user_setting('non.existent', 'default'))->toBe('default');
});

it('returns settings manager when setting helper called without arguments', function () {
    expect(setting())->toBeInstanceOf(\Stratos\Settings\Managers\SettingsManager::class);
});

it('returns user settings manager when user_setting helper called without arguments', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    expect(user_setting())->toBeInstanceOf(\Stratos\Settings\Managers\UserSettingsManager::class);
});
