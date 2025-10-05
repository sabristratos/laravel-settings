<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Stratos\Settings\Facades\Settings;
use Stratos\Settings\Models\Setting;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
    Cache::flush();
});

it('caches settings when cache is enabled', function () {
    config(['settings.cache.enabled' => true]);

    Settings::set('cached.key', 'cached value');

    $value1 = Settings::get('cached.key');

    $cacheKey = config('settings.cache.prefix', 'settings').':cached.key';
    expect(Cache::has($cacheKey))->toBeTrue();

    Setting::key('cached.key')->delete();

    $value2 = Settings::get('cached.key');

    expect($value2)->toBe('cached value');
});

it('does not cache when cache is disabled', function () {
    config(['settings.cache.enabled' => false]);

    Settings::set('uncached.key', 'uncached value');

    $value1 = Settings::get('uncached.key');

    $cacheKey = config('settings.cache.prefix', 'settings').':uncached.key';
    expect(Cache::has($cacheKey))->toBeFalse();
});

it('clears cache when setting is updated', function () {
    config(['settings.cache.enabled' => true]);

    Settings::set('update.test', 'original');
    Settings::get('update.test');

    $cacheKey = config('settings.cache.prefix', 'settings').':update.test';
    expect(Cache::has($cacheKey))->toBeTrue();

    Settings::set('update.test', 'updated');

    expect(Cache::has($cacheKey))->toBeFalse();

    expect(Settings::get('update.test'))->toBe('updated');
});

it('clears cache when setting is deleted', function () {
    config(['settings.cache.enabled' => true]);

    Settings::set('delete.test', 'value');
    Settings::get('delete.test');

    $cacheKey = config('settings.cache.prefix', 'settings').':delete.test';
    expect(Cache::has($cacheKey))->toBeTrue();

    Settings::forget('delete.test');

    expect(Cache::has($cacheKey))->toBeFalse();
});

it('uses configured cache driver', function () {
    config(['settings.cache.enabled' => true]);
    config(['settings.cache.driver' => 'array']);

    Settings::set('driver.test', 'value');
    Settings::get('driver.test');

    $cacheKey = config('settings.cache.prefix', 'settings').':driver.test';
    expect(Cache::driver('array')->has($cacheKey))->toBeTrue();
});

it('respects cache prefix configuration', function () {
    config(['settings.cache.enabled' => true]);
    config(['settings.cache.prefix' => 'custom_prefix']);

    Settings::set('prefix.test', 'value');
    Settings::get('prefix.test');

    expect(Cache::has('custom_prefix:prefix.test'))->toBeTrue();
});

it('caches has() method results', function () {
    config(['settings.cache.enabled' => true]);

    Settings::set('exists.test', 'value');

    $exists1 = Settings::has('exists.test');

    $cacheKey = config('settings.cache.prefix', 'settings').':exists:exists.test';
    expect(Cache::has($cacheKey))->toBeTrue();

    Setting::key('exists.test')->delete();

    $exists2 = Settings::has('exists.test');

    expect($exists2)->toBeTrue();
});

it('clears both value and exists cache on update', function () {
    config(['settings.cache.enabled' => true]);

    Settings::set('dual.cache', 'value');
    Settings::get('dual.cache');
    Settings::has('dual.cache');

    $valueCacheKey = config('settings.cache.prefix', 'settings').':dual.cache';
    $existsCacheKey = config('settings.cache.prefix', 'settings').':exists:dual.cache';

    expect(Cache::has($valueCacheKey))->toBeTrue();
    expect(Cache::has($existsCacheKey))->toBeTrue();

    Settings::set('dual.cache', 'updated');

    expect(Cache::has($valueCacheKey))->toBeFalse();
    expect(Cache::has($existsCacheKey))->toBeFalse();
});

it('can flush all settings cache', function () {
    config(['settings.cache.enabled' => true]);

    Settings::set('flush.test1', 'value1');
    Settings::set('flush.test2', 'value2');
    Settings::set('flush.test3', 'value3');

    Settings::get('flush.test1');
    Settings::get('flush.test2');
    Settings::get('flush.test3');

    Settings::flush();

    $prefix = config('settings.cache.prefix', 'settings');
    expect(Cache::has("{$prefix}:flush.test1"))->toBeFalse();
    expect(Cache::has("{$prefix}:flush.test2"))->toBeFalse();
    expect(Cache::has("{$prefix}:flush.test3"))->toBeFalse();
});

it('handles cache TTL configuration', function () {
    config(['settings.cache.enabled' => true]);
    config(['settings.cache.ttl' => 60]);

    Settings::set('ttl.test', 'value');

    expect(Settings::get('ttl.test'))->toBe('value');
});

it('returns default value from config when setting not found', function () {
    config(['settings.defaults' => ['fallback.key' => 'default value']]);

    expect(Settings::get('fallback.key'))->toBe('default value');
});
