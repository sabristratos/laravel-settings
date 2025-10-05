<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Strata\Settings\Facades\Settings;
use Strata\Settings\Models\Setting;
use Strata\Settings\Models\UserSetting;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
});

it('can encrypt a setting value', function () {
    Settings::setEncrypted('api.secret', 'super-secret-value');

    $setting = Setting::key('api.secret')->first();

    expect($setting->encrypted)->toBeTrue()
        ->and($setting->value)->not->toBe('super-secret-value')
        ->and($setting->type)->toBe('string');
});

it('can decrypt an encrypted setting', function () {
    Settings::setEncrypted('api.token', 'my-secret-token');

    $decrypted = Settings::encrypted('api.token');

    expect($decrypted)->toBe('my-secret-token');
});

it('can set encrypted value using set method with encrypted flag', function () {
    Settings::set('secret.key', 'secret-value', encrypted: true);

    $setting = Setting::key('secret.key')->first();

    expect($setting->encrypted)->toBeTrue();
    expect(Settings::encrypted('secret.key'))->toBe('secret-value');
});

it('returns default value when encrypted setting does not exist', function () {
    expect(Settings::encrypted('non.existent', 'default'))->toBe('default');
});

it('returns default value when setting is not encrypted', function () {
    Settings::set('plain.value', 'not encrypted');

    expect(Settings::encrypted('plain.value', 'default'))->toBe('default');
});

it('can update encrypted setting to unencrypted', function () {
    Settings::setEncrypted('convertable', 'secret');

    expect(Settings::encrypted('convertable'))->toBe('secret');

    Settings::set('convertable', 'plain text');

    $setting = Setting::key('convertable')->first();

    expect($setting->encrypted)->toBeFalse();
    expect(Settings::get('convertable'))->toBe('plain text');
});

it('can update unencrypted setting to encrypted', function () {
    Settings::set('convertable', 'plain');

    expect(Settings::get('convertable'))->toBe('plain');

    Settings::setEncrypted('convertable', 'now secret');

    $setting = Setting::key('convertable')->first();

    expect($setting->encrypted)->toBeTrue();
    expect(Settings::encrypted('convertable'))->toBe('now secret');
});

it('handles decryption failure gracefully', function () {
    $setting = Setting::create([
        'key' => 'corrupted.value',
        'value' => 'not-actually-encrypted',
        'encrypted' => true,
    ]);

    $value = $setting->getCastedValue();

    expect($value)->toBe('not-actually-encrypted');
});

it('returns default on decryption failure with encrypted method', function () {
    Setting::create([
        'key' => 'bad.encryption',
        'value' => 'invalid-encrypted-string',
        'encrypted' => true,
    ]);

    expect(Settings::encrypted('bad.encryption', 'fallback'))->toBe('fallback');
});

it('can encrypt user settings', function () {
    $user = User::factory()->create();

    Settings::user($user)->setEncrypted('private.key', 'user-secret');

    $userSetting = UserSetting::forUser($user->id)->key('private.key')->first();

    expect($userSetting->encrypted)->toBeTrue();
    expect($userSetting->value)->not->toBe('user-secret');
});

it('can decrypt user settings', function () {
    $user = User::factory()->create();

    Settings::user($user)->setEncrypted('auth.token', 'secure-token');

    $decrypted = Settings::user($user)->encrypted('auth.token');

    expect($decrypted)->toBe('secure-token');
});

it('maintains encryption flag when updating encrypted setting', function () {
    Settings::setEncrypted('persistent.encryption', 'value1');

    $setting = Setting::key('persistent.encryption')->first();
    expect($setting->encrypted)->toBeTrue();

    Settings::set('persistent.encryption', 'value2', encrypted: true);

    $setting->refresh();
    expect($setting->encrypted)->toBeTrue();
    expect(Settings::encrypted('persistent.encryption'))->toBe('value2');
});

it('stores encrypted values with correct type', function () {
    Settings::setEncrypted('typed.value', 'string value');

    $setting = Setting::key('typed.value')->first();

    expect($setting->type)->toBe('string');
});

it('can encrypt complex data structures', function () {
    $complexData = [
        'username' => 'admin',
        'password' => 'secret123',
        'api_key' => 'key-xyz',
    ];

    Settings::setEncrypted('credentials', json_encode($complexData));

    $decrypted = Settings::encrypted('credentials');
    $decoded = json_decode($decrypted, true);

    expect($decoded)->toBe($complexData);
});

it('handles empty string encryption', function () {
    Settings::setEncrypted('empty.value', '');

    expect(Settings::encrypted('empty.value'))->toBe('');
});

it('encrypts setting value at database level', function () {
    Settings::setEncrypted('db.encrypted', 'secret-data');

    $setting = Setting::key('db.encrypted')->first();

    expect($setting->getRawOriginal('value'))->not->toBe('secret-data');

    $decrypted = Crypt::decryptString($setting->getRawOriginal('value'));
    expect($decrypted)->toBe('secret-data');
});

it('uses setWithMetadata with encrypted flag', function () {
    Settings::setWithMetadata(
        key: 'metadata.encrypted',
        value: 'sensitive-data',
        label: ['en' => 'Sensitive Setting'],
        encrypted: true
    );

    $setting = Setting::key('metadata.encrypted')->first();

    expect($setting->encrypted)->toBeTrue();
    expect($setting->getTranslatedLabel())->toBe('Sensitive Setting');
    expect(Settings::encrypted('metadata.encrypted'))->toBe('sensitive-data');
});

it('user can update encrypted to unencrypted', function () {
    $user = User::factory()->create();

    Settings::user($user)->setEncrypted('toggle.encryption', 'secret');

    expect(Settings::user($user)->encrypted('toggle.encryption'))->toBe('secret');

    Settings::user($user)->set('toggle.encryption', 'plain');

    $userSetting = UserSetting::forUser($user->id)->key('toggle.encryption')->first();

    expect($userSetting->encrypted)->toBeFalse();
    expect(Settings::user($user)->get('toggle.encryption'))->toBe('plain');
});
