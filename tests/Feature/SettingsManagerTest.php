<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Stratos\Settings\Facades\Settings;
use Stratos\Settings\Models\Setting;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
});

it('can set and get a string setting', function () {
    Settings::set('app.name', 'Test Application');

    expect(Settings::get('app.name'))->toBe('Test Application');
});

it('can set and get an integer setting', function () {
    Settings::set('app.version', 1);

    expect(Settings::get('app.version'))->toBe(1);
});

it('can set and get a boolean setting', function () {
    Settings::set('app.enabled', true);

    expect(Settings::get('app.enabled'))->toBeTrue();
});

it('can set and get an array setting', function () {
    $data = ['one', 'two', 'three'];

    Settings::set('app.items', $data);

    expect(Settings::get('app.items'))->toBe($data);
});

it('returns default value when setting does not exist', function () {
    expect(Settings::get('non.existent', 'default'))->toBe('default');
});

it('can check if a setting exists', function () {
    Settings::set('test.key', 'value');

    expect(Settings::has('test.key'))->toBeTrue()
        ->and(Settings::has('non.existent'))->toBeFalse();
});

it('can delete a setting', function () {
    Settings::set('test.delete', 'value');
    expect(Settings::has('test.delete'))->toBeTrue();

    Settings::forget('test.delete');
    expect(Settings::has('test.delete'))->toBeFalse();
});

it('can get all settings', function () {
    Settings::set('key1', 'value1');
    Settings::set('key2', 'value2');
    Settings::set('key3', 'value3');

    $all = Settings::all();

    expect($all)->toHaveCount(3)
        ->and($all['key1'])->toBe('value1')
        ->and($all['key2'])->toBe('value2')
        ->and($all['key3'])->toBe('value3');
});

it('can get settings by group', function () {
    Settings::set('key1', 'value1', 'group1');
    Settings::set('key2', 'value2', 'group1');
    Settings::set('key3', 'value3', 'group2');

    $group1 = Settings::all('group1');

    expect($group1)->toHaveCount(2)
        ->and($group1['key1'])->toBe('value1')
        ->and($group1['key2'])->toBe('value2');
});

it('can set and retrieve encrypted settings', function () {
    Settings::setEncrypted('secret.key', 'sensitive-value');

    expect(Settings::encrypted('secret.key'))->toBe('sensitive-value');
});

it('stores encrypted value as encrypted in database', function () {
    Settings::setEncrypted('secret.api_key', 'my-secret-key');

    $setting = Setting::key('secret.api_key')->first();

    expect($setting->encrypted)->toBeTrue()
        ->and($setting->value)->not->toBe('my-secret-key');
});

it('can update existing setting', function () {
    Settings::set('test.update', 'original');
    expect(Settings::get('test.update'))->toBe('original');

    Settings::set('test.update', 'updated');
    expect(Settings::get('test.update'))->toBe('updated')
        ->and(Setting::key('test.update')->count())->toBe(1);
});

it('can change setting type when updating', function () {
    Settings::set('test.type', 'string value');
    expect(Settings::get('test.type'))->toBe('string value');

    Settings::set('test.type', 123);
    expect(Settings::get('test.type'))->toBe(123);
});
