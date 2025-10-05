<?php

namespace Strata\Settings\Contracts;

use Illuminate\Support\Collection;
use Stratos\Settings\Models\UserSetting;

interface UserSettingsManagerContract
{
    /**
     * Get a user setting value by key.
     */
    public function get(string $key, mixed $default = null): mixed;

    /**
     * Set a user setting value by key.
     */
    public function set(string $key, mixed $value, ?string $group = null, bool $encrypted = false): UserSetting;

    /**
     * Set a user setting with complete metadata.
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
    ): UserSetting;

    /**
     * Check if a user setting exists.
     */
    public function has(string $key): bool;

    /**
     * Delete a user setting by key.
     */
    public function forget(string $key): bool;

    /**
     * Get all user settings.
     */
    public function all(): Collection;

    /**
     * Get an encrypted user setting.
     */
    public function encrypted(string $key, mixed $default = null): mixed;

    /**
     * Flush all user settings from cache.
     */
    public function flush(): void;

    /**
     * Set multiple user settings at once.
     */
    public function setBulk(array $settings): Collection;

    /**
     * Get multiple user settings at once.
     */
    public function getBulk(array $keys, mixed $default = null): Collection;

    /**
     * Delete multiple user settings at once.
     */
    public function forgetBulk(array $keys): bool;

    /**
     * Set multiple user settings with metadata at once.
     */
    public function setWithMetadataBulk(array $settings): Collection;
}
