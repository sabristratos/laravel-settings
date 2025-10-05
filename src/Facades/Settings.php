<?php

namespace Strata\Settings\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static mixed get(string $key, mixed $default = null)
 * @method static \Strata\Settings\Models\Setting set(string $key, mixed $value, ?string $group = null, bool $encrypted = false)
 * @method static bool has(string $key)
 * @method static bool forget(string $key)
 * @method static \Illuminate\Support\Collection all(?string $group = null)
 * @method static \Illuminate\Support\Collection group(?string $group = null)
 * @method static void flush()
 * @method static mixed encrypted(string $key, mixed $default = null)
 * @method static \Strata\Settings\Models\Setting setEncrypted(string $key, mixed $value, ?string $group = null)
 * @method static \Strata\Settings\Managers\UserSettingsManager user($user = null)
 * @method static \Strata\Settings\Models\Setting setWithMetadata(string $key, mixed $value, ?string $group = null, array|string|null $label = null, array|string|null $description = null, ?array $validationRules = null, ?string $inputType = null, ?bool $isPublic = null, ?int $order = null, ?array $options = null, bool $encrypted = false)
 * @method static ?string getLabel(string $key, ?string $locale = null)
 * @method static ?string getDescription(string $key, ?string $locale = null)
 * @method static \Illuminate\Support\Collection allWithMetadata(?string $group = null)
 * @method static \Illuminate\Support\Collection allPublic(?string $group = null)
 * @method static \Illuminate\Support\Collection setBulk(array $settings)
 * @method static \Illuminate\Support\Collection getBulk(array $keys, mixed $default = null)
 * @method static bool forgetBulk(array $keys)
 * @method static \Illuminate\Support\Collection setWithMetadataBulk(array $settings)
 * @method static string export(string $format = 'json', array $options = [])
 * @method static void import(string $data, string $format = 'json', array $options = [])
 * @method static \Illuminate\Support\Collection getHistory(string $key, int $limit = 50)
 * @method static \Illuminate\Support\Collection getAllHistory(int $limit = 100)
 * @method static \Strata\Settings\Models\Setting restoreToVersion(string $key, int $historyId)
 *
 * @see \Strata\Settings\Managers\SettingsManager
 */
class Settings extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'settings';
    }
}
