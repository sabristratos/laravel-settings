<?php

use Stratos\Settings\Facades\Settings;

if (! function_exists('setting')) {
    /**
     * Get or set a setting value.
     *
     * @return mixed|\Stratos\Settings\Managers\SettingsManager
     */
    function setting(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return app('settings');
        }

        return Settings::get($key, $default);
    }
}

if (! function_exists('user_setting')) {
    /**
     * Get or set a user setting value.
     *
     * @return mixed|\Stratos\Settings\Managers\UserSettingsManager
     */
    function user_setting(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return Settings::user();
        }

        return Settings::user()->get($key, $default);
    }
}

if (! function_exists('setting_label')) {
    /**
     * Get the translated label for a setting.
     */
    function setting_label(string $key, ?string $locale = null): ?string
    {
        return Settings::getLabel($key, $locale);
    }
}

if (! function_exists('setting_description')) {
    /**
     * Get the translated description for a setting.
     */
    function setting_description(string $key, ?string $locale = null): ?string
    {
        return Settings::getDescription($key, $locale);
    }
}

if (! function_exists('user_setting_label')) {
    /**
     * Get the translated label for a user setting.
     */
    function user_setting_label(string $key, ?string $locale = null): ?string
    {
        return Settings::user()->getLabel($key, $locale);
    }
}

if (! function_exists('user_setting_description')) {
    /**
     * Get the translated description for a user setting.
     */
    function user_setting_description(string $key, ?string $locale = null): ?string
    {
        return Settings::user()->getDescription($key, $locale);
    }
}
