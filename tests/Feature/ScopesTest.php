<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Stratos\Settings\Facades\Settings;
use Stratos\Settings\Models\Setting;
use Stratos\Settings\Models\UserSetting;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
});

it('can filter settings by group using group scope', function () {
    Setting::create(['key' => 'site.name', 'value' => 'My Site', 'group' => 'site']);
    Setting::create(['key' => 'site.tagline', 'value' => 'Best Site', 'group' => 'site']);
    Setting::create(['key' => 'email.from', 'value' => 'test@example.com', 'group' => 'email']);
    Setting::create(['key' => 'system.debug', 'value' => 'false', 'group' => 'system']);

    $siteSettings = Setting::group('site')->get();

    expect($siteSettings)->toHaveCount(2);
    expect($siteSettings->pluck('key')->toArray())->toBe(['site.name', 'site.tagline']);
});

it('can filter settings by key using key scope', function () {
    Setting::create(['key' => 'unique.key', 'value' => 'value1']);
    Setting::create(['key' => 'another.key', 'value' => 'value2']);

    $setting = Setting::key('unique.key')->first();

    expect($setting)->not->toBeNull();
    expect($setting->value)->toBe('value1');
});

it('can filter public settings using public scope', function () {
    Setting::create(['key' => 'public.setting1', 'value' => 'value1', 'is_public' => true]);
    Setting::create(['key' => 'public.setting2', 'value' => 'value2', 'is_public' => true]);
    Setting::create(['key' => 'private.setting', 'value' => 'secret', 'is_public' => false]);

    $publicSettings = Setting::public()->get();

    expect($publicSettings)->toHaveCount(2);
    expect($publicSettings->pluck('key')->toArray())->toBe(['public.setting1', 'public.setting2']);
});

it('can order settings using ordered scope', function () {
    Setting::create(['key' => 'third', 'value' => 'c', 'order' => 3]);
    Setting::create(['key' => 'first', 'value' => 'a', 'order' => 1]);
    Setting::create(['key' => 'second', 'value' => 'b', 'order' => 2]);

    $orderedSettings = Setting::ordered()->get();

    expect($orderedSettings->pluck('key')->toArray())->toBe(['first', 'second', 'third']);
});

it('can combine group and ordered scopes', function () {
    Setting::create(['key' => 'site.footer', 'value' => 'footer', 'group' => 'site', 'order' => 2]);
    Setting::create(['key' => 'site.header', 'value' => 'header', 'group' => 'site', 'order' => 1]);
    Setting::create(['key' => 'email.from', 'value' => 'test@example.com', 'group' => 'email', 'order' => 1]);

    $siteSettings = Setting::group('site')->ordered()->get();

    expect($siteSettings)->toHaveCount(2);
    expect($siteSettings->pluck('key')->toArray())->toBe(['site.header', 'site.footer']);
});

it('can combine public and ordered scopes', function () {
    Setting::create(['key' => 'public.b', 'value' => 'b', 'is_public' => true, 'order' => 2]);
    Setting::create(['key' => 'public.a', 'value' => 'a', 'is_public' => true, 'order' => 1]);
    Setting::create(['key' => 'private.c', 'value' => 'c', 'is_public' => false, 'order' => 3]);

    $publicSettings = Setting::public()->ordered()->get();

    expect($publicSettings)->toHaveCount(2);
    expect($publicSettings->pluck('key')->toArray())->toBe(['public.a', 'public.b']);
});

it('can filter user settings by user using forUser scope', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    UserSetting::create(['user_id' => $user1->id, 'key' => 'theme', 'value' => 'dark']);
    UserSetting::create(['user_id' => $user1->id, 'key' => 'language', 'value' => 'en']);
    UserSetting::create(['user_id' => $user2->id, 'key' => 'theme', 'value' => 'light']);

    $user1Settings = UserSetting::forUser($user1->id)->get();

    expect($user1Settings)->toHaveCount(2);

    $keys = $user1Settings->pluck('key')->toArray();
    sort($keys);

    expect($keys)->toBe(['language', 'theme']);
});

it('can combine user and key scopes for user settings', function () {
    $user = User::factory()->create();

    UserSetting::create(['user_id' => $user->id, 'key' => 'theme', 'value' => 'dark']);
    UserSetting::create(['user_id' => $user->id, 'key' => 'language', 'value' => 'en']);

    $setting = UserSetting::forUser($user->id)->key('theme')->first();

    expect($setting)->not->toBeNull();
    expect($setting->value)->toBe('dark');
});

it('can order user settings', function () {
    $user = User::factory()->create();

    UserSetting::create(['user_id' => $user->id, 'key' => 'third', 'value' => 'c', 'order' => 3]);
    UserSetting::create(['user_id' => $user->id, 'key' => 'first', 'value' => 'a', 'order' => 1]);
    UserSetting::create(['user_id' => $user->id, 'key' => 'second', 'value' => 'b', 'order' => 2]);

    $orderedSettings = UserSetting::forUser($user->id)->ordered()->get();

    expect($orderedSettings->pluck('key')->toArray())->toBe(['first', 'second', 'third']);
});

it('uses allPublic method from settings manager', function () {
    Settings::setWithMetadata('public1', 'value1', isPublic: true);
    Settings::setWithMetadata('public2', 'value2', isPublic: true);
    Settings::setWithMetadata('private1', 'secret', isPublic: false);

    $publicSettings = Settings::allPublic();

    expect($publicSettings)->toHaveCount(2);
    expect($publicSettings->has('public1'))->toBeTrue();
    expect($publicSettings->has('public2'))->toBeTrue();
    expect($publicSettings->has('private1'))->toBeFalse();
});

it('uses allPublic with group filtering', function () {
    Settings::setWithMetadata('api.public.key', 'key1', group: 'api', isPublic: true);
    Settings::setWithMetadata('api.private.key', 'key2', group: 'api', isPublic: false);
    Settings::setWithMetadata('site.name', 'My Site', group: 'site', isPublic: true);

    $apiPublicSettings = Settings::allPublic('api');

    expect($apiPublicSettings)->toHaveCount(1);
    expect($apiPublicSettings->has('api.public.key'))->toBeTrue();
});

it('uses allWithMetadata for ordered settings', function () {
    Settings::setWithMetadata('key3', 'value3', order: 3);
    Settings::setWithMetadata('key1', 'value1', order: 1);
    Settings::setWithMetadata('key2', 'value2', order: 2);

    $settings = Settings::allWithMetadata();

    expect($settings->pluck('key')->toArray())->toBe(['key1', 'key2', 'key3']);
});

it('filters null groups correctly', function () {
    Setting::create(['key' => 'ungrouped1', 'value' => 'value1', 'group' => null]);
    Setting::create(['key' => 'ungrouped2', 'value' => 'value2', 'group' => null]);
    Setting::create(['key' => 'grouped', 'value' => 'value3', 'group' => 'test']);

    $ungroupedSettings = Setting::group(null)->get();

    expect($ungroupedSettings)->toHaveCount(2);
});

it('handles empty result sets gracefully', function () {
    $nonExistent = Setting::group('non-existent-group')->get();

    expect($nonExistent)->toHaveCount(0);
    expect($nonExistent)->toBeInstanceOf(\Illuminate\Database\Eloquent\Collection::class);
});

it('combines all scopes for complex queries', function () {
    Setting::create(['key' => 'match', 'value' => 'value', 'group' => 'test', 'is_public' => true, 'order' => 1]);
    Setting::create(['key' => 'no-match-private', 'value' => 'value', 'group' => 'test', 'is_public' => false, 'order' => 2]);
    Setting::create(['key' => 'no-match-group', 'value' => 'value', 'group' => 'other', 'is_public' => true, 'order' => 3]);

    $result = Setting::group('test')->public()->ordered()->get();

    expect($result)->toHaveCount(1);
    expect($result->first()->key)->toBe('match');
});
