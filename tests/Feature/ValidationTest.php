<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Stratos\Settings\Facades\Settings;
use Stratos\Settings\Models\Setting;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
});

it('can set validation rules on a setting', function () {
    Settings::setWithMetadata(
        key: 'email.from',
        value: 'test@example.com',
        validationRules: ['required', 'email']
    );

    $setting = Setting::key('email.from')->first();

    expect($setting->validation_rules)->toBe(['required', 'email']);
});

it('validates value when setting has validation rules', function () {
    Settings::setWithMetadata(
        key: 'email.from',
        value: 'test@example.com',
        validationRules: ['required', 'email']
    );

    Settings::set('email.from', 'invalid-email');
})->throws(ValidationException::class);

it('allows valid values that pass validation', function () {
    Settings::setWithMetadata(
        key: 'max_items',
        value: 50,
        validationRules: ['required', 'integer', 'min:1', 'max:100']
    );

    Settings::set('max_items', 75);

    expect(Settings::get('max_items'))->toBe(75);
});

it('rejects values that fail validation', function () {
    Settings::setWithMetadata(
        key: 'max_items',
        value: 50,
        validationRules: ['required', 'integer', 'min:1', 'max:100']
    );

    Settings::set('max_items', 150);
})->throws(ValidationException::class);

it('can validate using model validate method', function () {
    $setting = Setting::create([
        'key' => 'test.validation',
        'value' => 'test@example.com',
        'validation_rules' => ['required', 'email'],
    ]);

    expect($setting->validate('valid@example.com'))->toBeTrue()
        ->and($setting->validate('invalid-email'))->toBeFalse();
});

it('returns validation errors', function () {
    $setting = Setting::create([
        'key' => 'test.email',
        'value' => 'test@example.com',
        'validation_rules' => ['required', 'email'],
    ]);

    $errors = $setting->getValidationErrors('invalid-email');

    expect($errors)->toBeArray()
        ->and($errors)->not->toBeEmpty();
});

it('returns empty array when validation passes', function () {
    $setting = Setting::create([
        'key' => 'test.email',
        'value' => 'test@example.com',
        'validation_rules' => ['required', 'email'],
    ]);

    $errors = $setting->getValidationErrors('valid@example.com');

    expect($errors)->toBe([]);
});

it('can update validation rules', function () {
    Settings::setWithMetadata(
        key: 'test.field',
        value: 'value',
        validationRules: ['required', 'string']
    );

    Settings::setWithMetadata(
        key: 'test.field',
        value: 'new value',
        validationRules: ['required', 'string', 'max:50']
    );

    $setting = Setting::key('test.field')->first();

    expect($setting->validation_rules)->toBe(['required', 'string', 'max:50']);
});

it('validates user settings with validation rules', function () {
    $user = User::factory()->create();

    Settings::user($user)->setWithMetadata(
        key: 'theme',
        value: 'dark',
        validationRules: ['required', 'in:light,dark']
    );

    Settings::user($user)->set('theme', 'invalid');
})->throws(ValidationException::class);

it('allows valid user setting values', function () {
    $user = User::factory()->create();

    Settings::user($user)->setWithMetadata(
        key: 'theme',
        value: 'dark',
        validationRules: ['required', 'in:light,dark']
    );

    Settings::user($user)->set('theme', 'light');

    expect(Settings::user($user)->get('theme'))->toBe('light');
});

it('skips validation when encrypted is true', function () {
    Settings::setWithMetadata(
        key: 'api.key',
        value: 'test@example.com',
        validationRules: ['required', 'email'],
        encrypted: true
    );

    Settings::set('api.key', 'secret', encrypted: true);

    expect(Settings::encrypted('api.key'))->toBe('secret');
});

it('validates on setWithMetadata before saving', function () {
    Settings::setWithMetadata(
        key: 'test.number',
        value: 'not-a-number',
        validationRules: ['required', 'integer']
    );
})->throws(ValidationException::class);
