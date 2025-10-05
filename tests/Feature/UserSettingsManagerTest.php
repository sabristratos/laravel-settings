<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Strata\Settings\Facades\Settings;
use Strata\Settings\Models\UserSetting;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
});

it('can set and get user settings', function () {
    $user = User::factory()->create();

    Settings::user($user)->set('theme', 'dark');

    expect(Settings::user($user)->get('theme'))->toBe('dark');
});

it('can handle different data types for user settings', function () {
    $user = User::factory()->create();

    Settings::user($user)->set('notifications', true);
    Settings::user($user)->set('page_size', 50);
    Settings::user($user)->set('preferences', ['color' => 'blue', 'size' => 'large']);

    expect(Settings::user($user)->get('notifications'))->toBeTrue()
        ->and(Settings::user($user)->get('page_size'))->toBe(50)
        ->and(Settings::user($user)->get('preferences'))->toBe(['color' => 'blue', 'size' => 'large']);
});

it('returns default value when user setting does not exist', function () {
    $user = User::factory()->create();

    expect(Settings::user($user)->get('non.existent', 'default'))->toBe('default');
});

it('can check if user setting exists', function () {
    $user = User::factory()->create();

    Settings::user($user)->set('test.key', 'value');

    expect(Settings::user($user)->has('test.key'))->toBeTrue()
        ->and(Settings::user($user)->has('non.existent'))->toBeFalse();
});

it('can delete user setting', function () {
    $user = User::factory()->create();

    Settings::user($user)->set('test.delete', 'value');
    expect(Settings::user($user)->has('test.delete'))->toBeTrue();

    Settings::user($user)->forget('test.delete');
    expect(Settings::user($user)->has('test.delete'))->toBeFalse();
});

it('can get all user settings', function () {
    $user = User::factory()->create();

    Settings::user($user)->set('key1', 'value1');
    Settings::user($user)->set('key2', 'value2');
    Settings::user($user)->set('key3', 'value3');

    $all = Settings::user($user)->all();

    expect($all)->toHaveCount(3)
        ->and($all['key1'])->toBe('value1')
        ->and($all['key2'])->toBe('value2')
        ->and($all['key3'])->toBe('value3');
});

it('isolates settings between different users', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    Settings::user($user1)->set('theme', 'dark');
    Settings::user($user2)->set('theme', 'light');

    expect(Settings::user($user1)->get('theme'))->toBe('dark')
        ->and(Settings::user($user2)->get('theme'))->toBe('light');
});

it('can flush all user settings', function () {
    $user = User::factory()->create();

    Settings::user($user)->set('key1', 'value1');
    Settings::user($user)->set('key2', 'value2');
    Settings::user($user)->set('key3', 'value3');

    expect(Settings::user($user)->all())->toHaveCount(3);

    Settings::user($user)->flush();

    expect(Settings::user($user)->all())->toHaveCount(0);
});

it('can update existing user setting', function () {
    $user = User::factory()->create();

    Settings::user($user)->set('test.update', 'original');
    expect(Settings::user($user)->get('test.update'))->toBe('original');

    Settings::user($user)->set('test.update', 'updated');
    expect(Settings::user($user)->get('test.update'))->toBe('updated')
        ->and(UserSetting::forUser($user->id)->key('test.update')->count())->toBe(1);
});

it('uses authenticated user by default', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Settings::user()->set('auto.theme', 'dark');

    expect(Settings::user()->get('auto.theme'))->toBe('dark')
        ->and(UserSetting::forUser($user->id)->key('auto.theme')->exists())->toBeTrue();
});
