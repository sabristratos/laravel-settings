<?php

namespace Strata\Settings\Managers;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Validation\ValidationException;
use Stratos\Settings\Contracts\UserSettingsManagerContract;
use Stratos\Settings\Models\UserSetting;

class UserSettingsManager implements UserSettingsManagerContract
{
    /**
     * The user instance.
     */
    protected ?Authenticatable $user;

    /**
     * Create a new user settings manager instance.
     */
    public function __construct(?Authenticatable $user = null)
    {
        $this->user = $user ?? Auth::user();
    }

    /**
     * Get a user setting value by key.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        if (! $this->user) {
            return $default;
        }

        $setting = UserSetting::forUser($this->user->id)
            ->key($key)
            ->first();

        if (! $setting) {
            return $default;
        }

        return $setting->getCastedValue();
    }

    /**
     * Set a user setting value by key.
     */
    public function set(string $key, mixed $value, ?string $group = null, bool $encrypted = false): UserSetting
    {
        if (! $this->user) {
            throw new \RuntimeException('No authenticated user found.');
        }

        $setting = UserSetting::forUser($this->user->id)
            ->key($key)
            ->first();

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
            $setting = new UserSetting([
                'user_id' => $this->user->id,
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

        return $setting;
    }

    /**
     * Check if a user setting exists.
     */
    public function has(string $key): bool
    {
        if (! $this->user) {
            return false;
        }

        return UserSetting::forUser($this->user->id)
            ->key($key)
            ->exists();
    }

    /**
     * Delete a user setting by key.
     */
    public function forget(string $key): bool
    {
        if (! $this->user) {
            return false;
        }

        $result = UserSetting::forUser($this->user->id)
            ->key($key)
            ->delete();

        return (bool) $result;
    }

    /**
     * Get all user settings.
     */
    public function all(): Collection
    {
        if (! $this->user) {
            return collect();
        }

        return UserSetting::forUser($this->user->id)
            ->get()
            ->mapWithKeys(function (UserSetting $setting) {
                return [$setting->key => $setting->getCastedValue()];
            });
    }

    /**
     * Delete all settings for the user.
     */
    public function flush(): void
    {
        if (! $this->user) {
            return;
        }

        UserSetting::forUser($this->user->id)->delete();
    }

    /**
     * Set the user for this manager instance.
     */
    public function forUser(Authenticatable $user): static
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get an encrypted user setting value.
     */
    public function encrypted(string $key, mixed $default = null): mixed
    {
        if (! $this->user) {
            return $default;
        }

        $setting = UserSetting::forUser($this->user->id)
            ->key($key)
            ->first();

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
     * Set an encrypted user setting value.
     */
    public function setEncrypted(string $key, mixed $value, ?string $group = null): UserSetting
    {
        return $this->set($key, $value, $group, true);
    }

    /**
     * Set a user setting with full metadata.
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
    ): UserSetting {
        $inputType = $inputType ?? 'text';
        $order = $order ?? 0;
        if (! $this->user) {
            throw new \RuntimeException('No authenticated user found.');
        }

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

        $setting = UserSetting::forUser($this->user->id)
            ->key($key)
            ->first();

        if ($setting) {
            if ($encrypted) {
                $setting->value = Crypt::encryptString($value);
                $setting->encrypted = true;
                $setting->type = 'string';
            } else {
                $setting->setCastedValue($value);
                $setting->encrypted = false;
            }

            $setting->label = $label;
            $setting->description = $description;
            $setting->validation_rules = $validationRules;
            $setting->options = $options;
            $setting->input_type = $inputType;
            $setting->order = $order;

            $setting->save();
        } else {
            $setting = new UserSetting([
                'user_id' => $this->user->id,
                'key' => $key,
                'label' => $label,
                'description' => $description,
                'validation_rules' => $validationRules,
                'options' => $options,
                'input_type' => $inputType,
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

        return $setting;
    }

    /**
     * Get the translated label for a user setting.
     */
    public function getLabel(string $key, ?string $locale = null): ?string
    {
        if (! $this->user) {
            return null;
        }

        $setting = UserSetting::forUser($this->user->id)
            ->key($key)
            ->first();

        if (! $setting) {
            return null;
        }

        return $setting->getTranslatedLabel($locale);
    }

    /**
     * Get the translated description for a user setting.
     */
    public function getDescription(string $key, ?string $locale = null): ?string
    {
        if (! $this->user) {
            return null;
        }

        $setting = UserSetting::forUser($this->user->id)
            ->key($key)
            ->first();

        if (! $setting) {
            return null;
        }

        return $setting->getTranslatedDescription($locale);
    }

    /**
     * Get all user settings with their metadata.
     */
    public function allWithMetadata(): Collection
    {
        if (! $this->user) {
            return collect();
        }

        return UserSetting::forUser($this->user->id)
            ->ordered()
            ->get();
    }

    /**
     * Set multiple user settings at once.
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
     * Get multiple user settings at once.
     */
    public function getBulk(array $keys, mixed $default = null): Collection
    {
        return collect($keys)->mapWithKeys(function ($key) use ($default) {
            return [$key => $this->get($key, $default)];
        });
    }

    /**
     * Delete multiple user settings at once.
     */
    public function forgetBulk(array $keys): bool
    {
        $results = collect($keys)->map(fn ($key) => $this->forget($key));

        return $results->every(fn ($result) => $result === true);
    }

    /**
     * Set multiple user settings with metadata at once.
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
}
