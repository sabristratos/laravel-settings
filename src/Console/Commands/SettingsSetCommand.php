<?php

namespace Stratos\Settings\Console\Commands;

use Illuminate\Console\Command;
use Stratos\Settings\Facades\Settings;
use Stratos\Settings\Models\Setting;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

class SettingsSetCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'settings:set
                            {key? : The setting key}
                            {value? : The setting value}
                            {--group= : The setting group}
                            {--encrypted : Encrypt the value}';

    /**
     * The console command description.
     */
    protected $description = 'Set a setting value (interactive mode available)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $key = $this->argument('key');
        $value = $this->argument('value');
        $group = $this->option('group');
        $encrypted = $this->option('encrypted');

        if (! $key) {
            $key = text(
                label: 'Setting Key',
                placeholder: 'e.g., site.name, app.timezone, feature.enabled',
                required: true,
                validate: fn ($value) => $this->validateKey($value)
            );
        }

        $existingSetting = Setting::key($key)->first();
        if ($existingSetting && ! $value) {
            $this->warn("Setting '{$key}' already exists with value: ".json_encode($existingSetting->getCastedValue()));

            if (! confirm('Do you want to update it?', default: true)) {
                $this->info('Operation cancelled.');

                return self::SUCCESS;
            }
        }

        if (! $value) {
            $value = text(
                label: 'Setting Value',
                placeholder: 'Enter the value (supports: string, number, true/false, JSON)',
                required: true,
                hint: 'Auto-detects type: numbers, booleans (true/false), or JSON arrays/objects'
            );
        }

        $parsedValue = $this->parseValue($value);

        if ($group === null && ! $this->option('group')) {
            $availableGroups = $this->getAvailableGroups();

            $groupChoices = array_merge(
                ['none' => '(No Group)'],
                $availableGroups,
                ['custom' => '+ Enter Custom Group']
            );

            $selectedGroup = select(
                label: 'Setting Group',
                options: $groupChoices,
                default: 'none',
                hint: 'Groups help organize related settings'
            );

            if ($selectedGroup === 'custom') {
                $group = text(
                    label: 'Custom Group Name',
                    placeholder: 'e.g., api, email, features',
                    required: false
                );
            } elseif ($selectedGroup !== 'none') {
                $group = $selectedGroup;
            }
        }

        if (! $encrypted && ! $this->option('encrypted')) {
            $encrypted = confirm(
                label: 'Encrypt this value?',
                default: false,
                hint: 'Recommended for sensitive data like API keys, passwords, etc.'
            );
        }

        $this->displaySummary($key, $parsedValue, $group, $encrypted);

        if (! confirm('Save this setting?', default: true)) {
            $this->info('Operation cancelled.');

            return self::SUCCESS;
        }

        try {
            Settings::set($key, $parsedValue, $group, $encrypted);

            $this->components->success("Setting '{$key}' has been saved successfully.");

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->components->error("Failed to set setting: {$e->getMessage()}");

            return self::FAILURE;
        }
    }

    /**
     * Validate setting key.
     */
    protected function validateKey(string $key): ?string
    {
        if (empty(trim($key))) {
            return 'Setting key is required.';
        }

        if (strlen($key) > 255) {
            return 'Setting key must not exceed 255 characters.';
        }

        return null;
    }

    /**
     * Get available groups from config.
     */
    protected function getAvailableGroups(): array
    {
        return config('settings.groups', []);
    }

    /**
     * Display summary before saving.
     */
    protected function displaySummary(string $key, mixed $value, ?string $group, bool $encrypted): void
    {
        $type = gettype($value);
        if (is_array($value)) {
            $type = 'array ('.count($value).' items)';
        }

        $this->newLine();
        $this->components->twoColumnDetail('<fg=gray>Key</>', $key);
        $this->components->twoColumnDetail('<fg=gray>Value</>', $encrypted ? '<fg=yellow>***encrypted***</>' : json_encode($value));
        $this->components->twoColumnDetail('<fg=gray>Type</>', $type);
        $this->components->twoColumnDetail('<fg=gray>Group</>', $group ?? '<fg=gray>-</>');
        $this->components->twoColumnDetail('<fg=gray>Encrypted</>', $encrypted ? '<fg=green>Yes</>' : '<fg=gray>No</>');
        $this->newLine();
    }

    /**
     * Parse the value to detect data types.
     */
    protected function parseValue(string $value): mixed
    {
        if ($value === 'true' || $value === 'false') {
            return $value === 'true';
        }

        if (is_numeric($value)) {
            return str_contains($value, '.') ? (float) $value : (int) $value;
        }

        if (str_starts_with($value, '{') || str_starts_with($value, '[')) {
            $decoded = json_decode($value, true);

            return json_last_error() === JSON_ERROR_NONE ? $decoded : $value;
        }

        return $value;
    }
}
