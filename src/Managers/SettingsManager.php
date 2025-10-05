<?php

namespace Strata\Settings\Managers;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Validation\ValidationException;
use Strata\Settings\Contracts\SettingsManagerContract;
use Strata\Settings\Models\Setting;
use Symfony\Component\Yaml\Yaml;

class SettingsManager implements SettingsManagerContract
{
    /**
     * Get a setting value by key.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        if ($this->isCacheEnabled()) {
            return Cache::driver($this->getCacheDriver())
                ->remember(
                    $this->getCacheKey($key),
                    $this->getCacheTtl(),
                    fn () => $this->getFromDatabase($key, $default)
                );
        }

        return $this->getFromDatabase($key, $default);
    }

    /**
     * Set a setting value by key.
     */
    public function set(string $key, mixed $value, ?string $group = null, bool $encrypted = false): Setting
    {
        $setting = Setting::key($key)->first();

        if ($setting && $setting->validation_rules && ! $encrypted) {
            if (! $setting->validate($value)) {
                throw ValidationException::withMessages([
                    'value' => $setting->getValidationErrors($value),
                ]);
            }
        }

        if ($setting) {
            if ($encrypted) {
                $setting->value = Crypt::encryptString($value);
                $setting->encrypted = true;
                $setting->type = 'string';
            } else {
                $setting->setCastedValue($value);
                $setting->encrypted = false;
            }

            if ($group !== null) {
                $setting->group = $group;
            }

            $setting->save();
        } else {
            $setting = new Setting([
                'key' => $key,
                'group' => $group,
                'encrypted' => $encrypted,
            ]);

            if ($encrypted) {
                $setting->value = Crypt::encryptString($value);
                $setting->type = 'string';
            } else {
                $setting->setCastedValue($value);
            }

            $setting->save();
        }

        $this->clearCache($key);

        return $setting;
    }

    /**
     * Check if a setting exists.
     */
    public function has(string $key): bool
    {
        if ($this->isCacheEnabled()) {
            return Cache::driver($this->getCacheDriver())
                ->remember(
                    $this->getCacheKey("exists:{$key}"),
                    $this->getCacheTtl(),
                    fn () => Setting::key($key)->exists()
                );
        }

        return Setting::key($key)->exists();
    }

    /**
     * Delete a setting by key.
     */
    public function forget(string $key): bool
    {
        $result = Setting::key($key)->delete();

        $this->clearCache($key);

        return (bool) $result;
    }

    /**
     * Get all settings.
     */
    public function all(?string $group = null): Collection
    {
        $query = Setting::query();

        if ($group) {
            $query->group($group);
        }

        return $query->get()->mapWithKeys(function (Setting $setting) {
            return [$setting->key => $setting->getCastedValue()];
        });
    }

    /**
     * Get settings by group.
     */
    public function group(?string $group = null): Collection
    {
        return $this->all($group);
    }

    /**
     * Clear all cached settings.
     */
    public function flush(): void
    {
        if ($this->isCacheEnabled()) {
            Cache::driver($this->getCacheDriver())->flush();
        }
    }

    /**
     * Get an encrypted setting value.
     */
    public function encrypted(string $key, mixed $default = null): mixed
    {
        $setting = Setting::key($key)->first();

        if (! $setting) {
            return $default;
        }

        if (! $setting->encrypted) {
            return $default;
        }

        try {
            return Crypt::decryptString($setting->value);
        } catch (\Exception $e) {
            return $default;
        }
    }

    /**
     * Set an encrypted setting value.
     */
    public function setEncrypted(string $key, mixed $value, ?string $group = null): Setting
    {
        return $this->set($key, $value, $group, true);
    }

    /**
     * Get a user settings manager instance.
     */
    public function user($user = null): UserSettingsManager
    {
        return new UserSettingsManager($user);
    }

    /**
     * Set a setting with full metadata.
     */
    public function setWithMetadata(
        string $key,
        mixed $value,
        ?string $group = null,
        array|string|null $label = null,
        array|string|null $description = null,
        ?array $validationRules = null,
        ?string $inputType = null,
        ?bool $isPublic = null,
        ?int $order = null,
        ?array $options = null,
        bool $encrypted = false
    ): Setting {
        $inputType = $inputType ?? 'text';
        $isPublic = $isPublic ?? false;
        $order = $order ?? 0;
        if ($validationRules && ! $encrypted) {
            $validator = \Illuminate\Support\Facades\Validator::make(
                ['value' => $value],
                ['value' => $validationRules]
            );

            if ($validator->fails()) {
                throw ValidationException::withMessages([
                    'value' => $validator->errors()->get('value'),
                ]);
            }
        }

        $setting = Setting::key($key)->first();

        if ($setting) {
            if ($encrypted) {
                $setting->value = Crypt::encryptString($value);
                $setting->encrypted = true;
                $setting->type = 'string';
            } else {
                $setting->setCastedValue($value);
                $setting->encrypted = false;
            }

            $setting->group = $group;
            $setting->label = $label;
            $setting->description = $description;
            $setting->validation_rules = $validationRules;
            $setting->options = $options;
            $setting->input_type = $inputType;
            $setting->is_public = $isPublic;
            $setting->order = $order;

            $setting->save();
        } else {
            $setting = new Setting([
                'key' => $key,
                'group' => $group,
                'label' => $label,
                'description' => $description,
                'validation_rules' => $validationRules,
                'options' => $options,
                'input_type' => $inputType,
                'is_public' => $isPublic,
                'order' => $order,
                'encrypted' => $encrypted,
            ]);

            if ($encrypted) {
                $setting->value = Crypt::encryptString($value);
                $setting->type = 'string';
            } else {
                $setting->setCastedValue($value);
            }

            $setting->save();
        }

        $this->clearCache($key);

        return $setting;
    }

    /**
     * Get the translated label for a setting.
     */
    public function getLabel(string $key, ?string $locale = null): ?string
    {
        $setting = Setting::key($key)->first();

        if (! $setting) {
            return null;
        }

        return $setting->getTranslatedLabel($locale);
    }

    /**
     * Get the translated description for a setting.
     */
    public function getDescription(string $key, ?string $locale = null): ?string
    {
        $setting = Setting::key($key)->first();

        if (! $setting) {
            return null;
        }

        return $setting->getTranslatedDescription($locale);
    }

    /**
     * Get all settings with their metadata.
     */
    public function allWithMetadata(?string $group = null): Collection
    {
        $query = Setting::query();

        if ($group) {
            $query->group($group);
        }

        return $query->ordered()->get();
    }

    /**
     * Get all public settings.
     */
    public function allPublic(?string $group = null): Collection
    {
        $query = Setting::public();

        if ($group) {
            $query->group($group);
        }

        return $query->ordered()->get()->mapWithKeys(function (Setting $setting) {
            return [$setting->key => $setting->getCastedValue()];
        });
    }

    /**
     * Set multiple settings at once.
     */
    public function setBulk(array $settings): Collection
    {
        $results = collect();

        foreach ($settings as $key => $value) {
            $group = is_array($value) && isset($value['group']) ? $value['group'] : null;
            $encrypted = is_array($value) && isset($value['encrypted']) ? $value['encrypted'] : false;
            $actualValue = is_array($value) && isset($value['value']) ? $value['value'] : $value;

            $results->put($key, $this->set($key, $actualValue, $group, $encrypted));
        }

        return $results;
    }

    /**
     * Get multiple settings at once.
     */
    public function getBulk(array $keys, mixed $default = null): Collection
    {
        return collect($keys)->mapWithKeys(function ($key) use ($default) {
            return [$key => $this->get($key, $default)];
        });
    }

    /**
     * Delete multiple settings at once.
     */
    public function forgetBulk(array $keys): bool
    {
        $results = collect($keys)->map(fn ($key) => $this->forget($key));

        return $results->every(fn ($result) => $result === true);
    }

    /**
     * Set multiple settings with metadata at once.
     */
    public function setWithMetadataBulk(array $settings): Collection
    {
        $results = collect();

        foreach ($settings as $key => $data) {
            $results->put($key, $this->setWithMetadata(
                key: $key,
                value: $data['value'] ?? null,
                group: $data['group'] ?? null,
                label: $data['label'] ?? null,
                description: $data['description'] ?? null,
                validationRules: $data['validation_rules'] ?? null,
                inputType: $data['input_type'] ?? null,
                isPublic: $data['is_public'] ?? null,
                order: $data['order'] ?? null,
                options: $data['options'] ?? null,
                encrypted: $data['encrypted'] ?? false
            ));
        }

        return $results;
    }

    /**
     * Export settings to JSON or YAML.
     */
    public function export(string $format = 'json', array $options = []): string
    {
        $includeMetadata = $options['include_metadata'] ?? config('settings.import_export.include_metadata', true);
        $includeEncrypted = $options['include_encrypted'] ?? config('settings.import_export.include_encrypted', false);
        $group = $options['group'] ?? null;

        $query = Setting::query();

        if ($group) {
            $query->group($group);
        }

        if (! $includeEncrypted) {
            $query->where('encrypted', false);
        }

        $settings = $query->ordered()->get();

        $data = $settings->map(function (Setting $setting) use ($includeMetadata) {
            if ($includeMetadata) {
                return $setting->toArray();
            }

            return [
                'key' => $setting->key,
                'value' => $setting->getCastedValue(),
                'group' => $setting->group,
            ];
        })->all();

        return match ($format) {
            'yaml' => Yaml::dump($data, 4, 2),
            'json' => json_encode($data, JSON_PRETTY_PRINT),
            default => throw new \InvalidArgumentException("Unsupported format: {$format}")
        };
    }

    /**
     * Import settings from JSON or YAML.
     */
    public function import(string $data, string $format = 'json', array $options = []): void
    {
        $overwrite = $options['overwrite'] ?? true;

        $settings = match ($format) {
            'yaml' => Yaml::parse($data),
            'json' => json_decode($data, true),
            default => throw new \InvalidArgumentException("Unsupported format: {$format}")
        };

        if (! is_array($settings)) {
            throw new \InvalidArgumentException('Invalid import data format');
        }

        foreach ($settings as $setting) {
            if (! isset($setting['key'])) {
                continue;
            }

            if (! $overwrite && Setting::key($setting['key'])->exists()) {
                continue;
            }

            if (isset($setting['label']) || isset($setting['description']) || isset($setting['validation_rules'])) {
                $this->setWithMetadata(
                    key: $setting['key'],
                    value: $setting['value'] ?? null,
                    group: $setting['group'] ?? null,
                    label: $setting['label'] ?? null,
                    description: $setting['description'] ?? null,
                    validationRules: $setting['validation_rules'] ?? null,
                    inputType: $setting['input_type'] ?? null,
                    isPublic: $setting['is_public'] ?? null,
                    order: $setting['order'] ?? null,
                    options: $setting['options'] ?? null,
                    encrypted: $setting['encrypted'] ?? false
                );
            } else {
                $this->set(
                    key: $setting['key'],
                    value: $setting['value'] ?? null,
                    group: $setting['group'] ?? null,
                    encrypted: $setting['encrypted'] ?? false
                );
            }
        }
    }

    /**
     * Get setting change history.
     */
    public function getHistory(string $key, int $limit = 50): Collection
    {
        $historyModel = \Strata\Settings\Models\SettingHistory::class;

        return $historyModel::forKey($key)
            ->recent()
            ->limit($limit)
            ->get();
    }

    /**
     * Get all setting change history.
     */
    public function getAllHistory(int $limit = 100): Collection
    {
        $historyModel = \Strata\Settings\Models\SettingHistory::class;

        return $historyModel::recent()
            ->limit($limit)
            ->get();
    }

    /**
     * Restore a setting to a previous version.
     */
    public function restoreToVersion(string $key, int $historyId): Setting
    {
        $historyModel = \Strata\Settings\Models\SettingHistory::class;

        $history = $historyModel::findOrFail($historyId);

        if ($history->setting_key !== $key) {
            throw new \InvalidArgumentException("History record does not match setting key: {$key}");
        }

        $setting = Setting::key($key)->first();

        if (! $setting) {
            $setting = new Setting(['key' => $key]);
        }

        $value = $history->old_value;
        $type = $history->old_type;

        if ($type === 'array' || $type === 'object') {
            $value = json_decode($value, true);
        } elseif ($type === 'integer') {
            $value = (int) $value;
        } elseif ($type === 'float' || $type === 'double') {
            $value = (float) $value;
        } elseif ($type === 'boolean') {
            $value = (bool) $value;
        }

        $setting->setCastedValue($value);
        $setting->save();

        $this->clearCache($key);

        return $setting;
    }

    /**
     * Get setting value from database.
     */
    protected function getFromDatabase(string $key, mixed $default = null): mixed
    {
        $setting = Setting::key($key)->first();

        if (! $setting) {
            return $this->getDefaultValue($key, $default);
        }

        return $setting->getCastedValue();
    }

    /**
     * Get default value from config or provided default.
     */
    protected function getDefaultValue(string $key, mixed $default = null): mixed
    {
        $defaults = config('settings.defaults', []);

        return $defaults[$key] ?? $default;
    }

    /**
     * Clear cache for a specific key.
     */
    protected function clearCache(string $key): void
    {
        if ($this->isCacheEnabled()) {
            Cache::driver($this->getCacheDriver())->forget($this->getCacheKey($key));
            Cache::driver($this->getCacheDriver())->forget($this->getCacheKey("exists:{$key}"));
        }
    }

    /**
     * Get the cache key for a setting.
     */
    protected function getCacheKey(string $key): string
    {
        $prefix = config('settings.cache.prefix', 'settings');

        return "{$prefix}:{$key}";
    }

    /**
     * Check if caching is enabled.
     */
    protected function isCacheEnabled(): bool
    {
        return config('settings.cache.enabled', true);
    }

    /**
     * Get the cache driver.
     */
    protected function getCacheDriver(): string
    {
        return config('settings.cache.driver', config('cache.default'));
    }

    /**
     * Get the cache TTL.
     */
    protected function getCacheTtl(): int
    {
        return (int) config('settings.cache.ttl', 3600);
    }
}
