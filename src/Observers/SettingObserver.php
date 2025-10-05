<?php

namespace Stratos\Settings\Observers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Request;
use Stratos\Settings\Events\SettingCreated;
use Stratos\Settings\Events\SettingDeleted;
use Stratos\Settings\Events\SettingUpdated;
use Stratos\Settings\Models\Setting;
use Stratos\Settings\Models\SettingHistory;

class SettingObserver
{
    /**
     * Handle the Setting "created" event.
     */
    public function created(Setting $setting): void
    {
        $this->clearSettingCache($setting->key);

        $this->recordHistory($setting, null, $setting->value, 'created');

        event(new SettingCreated($setting));
    }

    /**
     * Handle the Setting "updated" event.
     */
    public function updated(Setting $setting): void
    {
        $oldValue = $setting->getOriginal('value');
        $oldType = $setting->getOriginal('type');

        $this->clearSettingCache($setting->key);

        $this->recordHistory($setting, $oldValue, $setting->value, 'updated', $oldType);

        event(new SettingUpdated($setting, $oldValue));
    }

    /**
     * Handle the Setting "deleted" event.
     */
    public function deleted(Setting $setting): void
    {
        $this->clearSettingCache($setting->key);

        $this->recordHistory($setting, $setting->value, null, 'deleted');

        event(new SettingDeleted($setting));
    }

    /**
     * Record setting change in history.
     */
    protected function recordHistory(Setting $setting, mixed $oldValue, mixed $newValue, string $action, ?string $oldType = null): void
    {
        if (! config('settings.audit.enabled', true)) {
            return;
        }

        $data = [
            'setting_key' => $setting->key,
            'old_value' => $this->serializeValue($oldValue),
            'new_value' => $this->serializeValue($newValue),
            'old_type' => $oldType ?? $setting->type,
            'new_type' => $setting->type,
            'action' => $action,
        ];

        if (config('settings.audit.track_ip', true)) {
            $data['ip_address'] = Request::ip();
        }

        if (config('settings.audit.track_user_agent', true)) {
            $data['user_agent'] = Request::userAgent();
        }

        if (Auth::check()) {
            $data['user_id'] = Auth::id();
        }

        SettingHistory::create($data);
    }

    /**
     * Serialize value for storage.
     */
    protected function serializeValue(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_array($value) || is_object($value)) {
            return json_encode($value);
        }

        return (string) $value;
    }

    /**
     * Clear cache for a specific setting key.
     */
    protected function clearSettingCache(string $key): void
    {
        if (! config('settings.cache.enabled', true)) {
            return;
        }

        $driver = Cache::driver(config('settings.cache.driver', config('cache.default')));
        $prefix = config('settings.cache.prefix', 'settings');

        $driver->forget("{$prefix}:{$key}");
        $driver->forget("{$prefix}:exists:{$key}");
    }
}
