<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Stratos\Settings\Facades\Settings;
use Stratos\Settings\Models\Setting;
use Stratos\Settings\Models\UserSetting;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
    config(['settings.default_locale' => 'en']);
    config(['settings.locales' => ['en', 'de', 'fr', 'es']]);
});

it('can store multilingual labels', function () {
    Settings::setWithMetadata(
        key: 'site.name',
        value: 'My Site',
        label: [
            'en' => 'Site Name',
            'de' => 'Seitenname',
            'fr' => 'Nom du site',
        ]
    );

    $setting = Setting::key('site.name')->first();

    expect($setting->label)->toBe([
        'en' => 'Site Name',
        'de' => 'Seitenname',
        'fr' => 'Nom du site',
    ]);
});

it('can retrieve translated label for current locale', function () {
    $setting = Setting::create([
        'key' => 'test.label',
        'value' => 'value',
        'label' => [
            'en' => 'English Label',
            'de' => 'German Label',
        ],
    ]);

    app()->setLocale('en');
    expect($setting->getTranslatedLabel())->toBe('English Label');

    app()->setLocale('de');
    expect($setting->getTranslatedLabel())->toBe('German Label');
});

it('can retrieve translated label for specific locale', function () {
    $setting = Setting::create([
        'key' => 'test.label',
        'value' => 'value',
        'label' => [
            'en' => 'English Label',
            'de' => 'German Label',
            'fr' => 'French Label',
        ],
    ]);

    expect($setting->getTranslatedLabel('en'))->toBe('English Label');
    expect($setting->getTranslatedLabel('de'))->toBe('German Label');
    expect($setting->getTranslatedLabel('fr'))->toBe('French Label');
});

it('falls back to default locale when translation is missing', function () {
    config(['settings.default_locale' => 'en']);

    $setting = Setting::create([
        'key' => 'test.fallback',
        'value' => 'value',
        'label' => [
            'en' => 'English Label',
        ],
    ]);

    app()->setLocale('es');
    expect($setting->getTranslatedLabel())->toBe('English Label');
});

it('returns null when label is not set', function () {
    $setting = Setting::create([
        'key' => 'test.nolabel',
        'value' => 'value',
    ]);

    expect($setting->getTranslatedLabel())->toBeNull();
});

it('can store and retrieve multilingual descriptions', function () {
    Settings::setWithMetadata(
        key: 'api.key',
        value: 'key123',
        description: [
            'en' => 'API Key for external services',
            'de' => 'API-Schlüssel für externe Dienste',
        ]
    );

    $setting = Setting::key('api.key')->first();

    app()->setLocale('en');
    expect($setting->getTranslatedDescription())->toBe('API Key for external services');

    app()->setLocale('de');
    expect($setting->getTranslatedDescription())->toBe('API-Schlüssel für externe Dienste');
});

it('can retrieve description for specific locale', function () {
    $setting = Setting::create([
        'key' => 'test.description',
        'value' => 'value',
        'description' => [
            'en' => 'English Description',
            'de' => 'German Description',
        ],
    ]);

    expect($setting->getTranslatedDescription('en'))->toBe('English Description');
    expect($setting->getTranslatedDescription('de'))->toBe('German Description');
});

it('can store and retrieve multilingual options', function () {
    $setting = Setting::create([
        'key' => 'theme.options',
        'value' => 'light',
        'options' => [
            'en' => ['light' => 'Light Mode', 'dark' => 'Dark Mode'],
            'de' => ['light' => 'Heller Modus', 'dark' => 'Dunkler Modus'],
        ],
    ]);

    app()->setLocale('en');
    expect($setting->getTranslatedOptions())->toBe(['light' => 'Light Mode', 'dark' => 'Dark Mode']);

    app()->setLocale('de');
    expect($setting->getTranslatedOptions())->toBe(['light' => 'Heller Modus', 'dark' => 'Dunkler Modus']);
});

it('can use helper functions to get translated labels', function () {
    Settings::setWithMetadata(
        key: 'helper.label',
        value: 'value',
        label: [
            'en' => 'English Helper Label',
            'de' => 'German Helper Label',
        ]
    );

    app()->setLocale('en');
    expect(setting_label('helper.label'))->toBe('English Helper Label');

    app()->setLocale('de');
    expect(setting_label('helper.label'))->toBe('German Helper Label');
});

it('can use helper functions to get translated descriptions', function () {
    Settings::setWithMetadata(
        key: 'helper.description',
        value: 'value',
        description: [
            'en' => 'English Description',
            'de' => 'German Description',
        ]
    );

    app()->setLocale('en');
    expect(setting_description('helper.description'))->toBe('English Description');

    expect(setting_description('helper.description', 'de'))->toBe('German Description');
});

it('supports translations for user settings', function () {
    $user = User::factory()->create();

    Settings::user($user)->setWithMetadata(
        key: 'user.preference',
        value: 'value',
        label: [
            'en' => 'User Preference',
            'de' => 'Benutzerpräferenz',
        ]
    );

    $userSetting = UserSetting::forUser($user->id)->key('user.preference')->first();

    app()->setLocale('en');
    expect($userSetting->getTranslatedLabel())->toBe('User Preference');

    app()->setLocale('de');
    expect($userSetting->getTranslatedLabel())->toBe('Benutzerpräferenz');
});

it('can use user setting helper functions for translations', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Settings::user()->setWithMetadata(
        key: 'user.label.test',
        value: 'value',
        label: [
            'en' => 'English User Label',
            'de' => 'German User Label',
        ],
        description: [
            'en' => 'English User Description',
            'de' => 'German User Description',
        ]
    );

    app()->setLocale('en');
    expect(user_setting_label('user.label.test'))->toBe('English User Label');
    expect(user_setting_description('user.label.test'))->toBe('English User Description');

    app()->setLocale('de');
    expect(user_setting_label('user.label.test'))->toBe('German User Label');
    expect(user_setting_description('user.label.test'))->toBe('German User Description');
});

it('handles missing locale gracefully for options', function () {
    $setting = Setting::create([
        'key' => 'test.options',
        'value' => 'value',
        'options' => [
            'en' => ['option1' => 'Option 1'],
        ],
    ]);

    app()->setLocale('es');
    expect($setting->getTranslatedOptions())->toBe(['option1' => 'Option 1']);
});
