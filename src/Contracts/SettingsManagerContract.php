<?php

namespace Strata\Settings\Contracts;

use Illuminate\Support\Collection;
use Stratos\Settings\Models\Setting;

interface SettingsManagerContract
{
    /**
     * Get a setting value by key.
     */
    public function get(string $key, mixed $default = null): mixed;

    /**
     * Set a setting value by key.
     */
    public function set(string $key, mixed $value, ?string $group = null, bool $encrypted = false): Setting;

    /**
     * Set a setting with complete metadata.
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
    ): Setting;

    /**
     * Check if a setting exists.
     */
    public function has(string $key): bool;

    /**
     * Delete a setting by key.
     */
    public function forget(string $key): bool;

    /**
     * Get all settings.
     */
    public function all(): Collection;

    /**
     * Get settings by group.
     */
    public function group(?string $group = null): Collection;

    /**
     * Get all public settings.
     */
    public function allPublic(?string $group = null): Collection;

    /**
     * Get all settings with metadata.
     */
    public function allWithMetadata(?string $group = null): Collection;

    /**
     * Set an encrypted setting.
     */
    public function encrypted(string $key, mixed $default = null): mixed;

    /**
     * Flush all settings from cache.
     */
    public function flush(): void;

    /**
     * Set multiple settings at once.
     */
    public function setBulk(array $settings): Collection;

    /**
     * Get multiple settings at once.
     */
    public function getBulk(array $keys, mixed $default = null): Collection;

    /**
     * Delete multiple settings at once.
     */
    public function forgetBulk(array $keys): bool;

    /**
     * Set multiple settings with metadata at once.
     */
    public function setWithMetadataBulk(array $settings): Collection;

    /**
     * Export settings to JSON or YAML.
     */
    public function export(string $format = 'json', array $options = []): string;

    /**
     * Import settings from JSON or YAML.
     */
    public function import(string $data, string $format = 'json', array $options = []): void;

    /**
     * Get setting change history.
     */
    public function getHistory(string $key, int $limit = 50): Collection;

    /**
     * Get all setting change history.
     */
    public function getAllHistory(int $limit = 100): Collection;

    /**
     * Restore a setting to a previous version.
     */
    public function restoreToVersion(string $key, int $historyId): Setting;
}
