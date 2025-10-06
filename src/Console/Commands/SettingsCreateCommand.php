<?php

namespace Stratos\Settings\Console\Commands;

use Illuminate\Console\Command;
use Stratos\Settings\Facades\Settings;
use Stratos\Settings\Models\Setting;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\multiselect;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;
use function Laravel\Prompts\textarea;

class SettingsCreateCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'settings:create';

    /**
     * The console command description.
     */
    protected $description = 'Create a new setting with complete metadata (interactive wizard)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->components->info('🚀 Settings Creation Wizard');
        $this->newLine();

        $key = text(
            label: 'Setting Key',
            placeholder: 'e.g., site.name, api.key, feature.enabled',
            required: true,
            validate: fn ($value) => $this->validateUniqueKey($value)
        );

        $value = text(
            label: 'Setting Value',
            placeholder: 'Enter the value (supports: string, number, true/false, JSON)',
            required: true,
            hint: 'Auto-detects type: numbers, booleans, or JSON arrays/objects'
        );

        $parsedValue = $this->parseValue($value);

        $withMetadata = confirm(
            label: 'Add metadata (labels, descriptions, validation, etc.)?',
            default: true,
            hint: 'Metadata helps with admin UIs and documentation'
        );

        $metadata = [];

        if ($withMetadata) {
            $metadata = $this->collectMetadata($key);
        }

        $group = $this->selectGroup();

        $encrypted = confirm(
            label: 'Encrypt this value?',
            default: false,
            hint: 'Recommended for sensitive data like API keys, passwords, etc.'
        );

        $this->displayCompleteSummary($key, $parsedValue, $group, $encrypted, $metadata);

        if (! confirm('Create this setting?', default: true)) {
            $this->components->warn('Operation cancelled.');

            return self::SUCCESS;
        }

        try {
            if ($withMetadata) {
                Settings::setWithMetadata(
                    key: $key,
                    value: $parsedValue,
                    group: $group,
                    label: $metadata['label'] ?? null,
                    description: $metadata['description'] ?? null,
                    validationRules: $metadata['validation_rules'] ?? null,
                    inputType: $metadata['input_type'] ?? null,
                    isPublic: $metadata['is_public'] ?? null,
                    order: $metadata['order'] ?? null,
                    options: $metadata['options'] ?? null,
                    encrypted: $encrypted
                );
            } else {
                Settings::set($key, $parsedValue, $group, $encrypted);
            }

            $this->components->success("Setting '{$key}' has been created successfully!");

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->components->error("Failed to create setting: {$e->getMessage()}");

            return self::FAILURE;
        }
    }

    /**
     * Validate that key is unique.
     */
    protected function validateUniqueKey(string $key): ?string
    {
        if (empty(trim($key))) {
            return 'Setting key is required.';
        }

        if (strlen($key) > 255) {
            return 'Setting key must not exceed 255 characters.';
        }

        if (Setting::key($key)->exists()) {
            return "Setting '{$key}' already exists. Use 'settings:set' to update it.";
        }

        return null;
    }

    /**
     * Collect metadata from user.
     */
    protected function collectMetadata(string $key): array
    {
        $metadata = [];

        $this->newLine();
        $this->components->info('📝 Setting Metadata');
        $this->newLine();

        if (confirm('Add label?', default: true)) {
            $labelType = select(
                label: 'Label Type',
                options: [
                    'simple' => 'Simple (single language)',
                    'multi' => 'Multilingual',
                ],
                default: 'simple'
            );

            if ($labelType === 'simple') {
                $metadata['label'] = text(
                    label: 'Label',
                    placeholder: 'e.g., Site Name, API Key, Enable Feature',
                    default: $this->generateDefaultLabel($key)
                );
            } else {
                $locales = config('settings.locales', ['en']);
                $multilangLabel = [];

                foreach ($locales as $locale) {
                    $label = text(
                        label: "Label ({$locale})",
                        placeholder: "Label in {$locale}",
                        required: $locale === $locales[0]
                    );

                    if ($label) {
                        $multilangLabel[$locale] = $label;
                    }
                }

                $metadata['label'] = $multilangLabel;
            }
        }

        if (confirm('Add description?', default: false)) {
            $metadata['description'] = textarea(
                label: 'Description',
                placeholder: 'Describe what this setting controls...',
                hint: 'Helps other developers understand the setting'
            );
        }

        $metadata['input_type'] = select(
            label: 'Input Type',
            options: [
                'text' => 'Text',
                'textarea' => 'Textarea',
                'number' => 'Number',
                'email' => 'Email',
                'url' => 'URL',
                'tel' => 'Phone',
                'password' => 'Password',
                'select' => 'Select Dropdown',
                'checkbox' => 'Checkbox',
                'radio' => 'Radio Buttons',
                'color' => 'Color Picker',
                'date' => 'Date',
            ],
            default: 'text',
            hint: 'How this setting should be displayed in admin UIs'
        );

        if (in_array($metadata['input_type'], ['select', 'radio'])) {
            $optionsInput = text(
                label: 'Options (JSON format)',
                placeholder: '{"value1":"Label 1","value2":"Label 2"}',
                hint: 'Provide options as JSON object'
            );

            if ($optionsInput) {
                $metadata['options'] = json_decode($optionsInput, true) ?? [];
            }
        }

        if (confirm('Add validation rules?', default: false)) {
            $rules = multiselect(
                label: 'Validation Rules',
                options: [
                    'required' => 'Required',
                    'email' => 'Email',
                    'url' => 'URL',
                    'numeric' => 'Numeric',
                    'integer' => 'Integer',
                    'boolean' => 'Boolean',
                    'json' => 'JSON',
                    'custom' => '+ Add Custom Rule',
                ],
                hint: 'Select applicable validation rules'
            );

            $validationRules = array_values($rules);

            if (in_array('custom', $rules)) {
                $customRule = text(
                    label: 'Custom Validation Rule',
                    placeholder: 'e.g., max:255, min:1, regex:/pattern/',
                    hint: 'Laravel validation rule syntax'
                );

                if ($customRule) {
                    $validationRules = array_filter($validationRules, fn ($r) => $r !== 'custom');
                    $validationRules[] = $customRule;
                }
            }

            $metadata['validation_rules'] = $validationRules;
        }

        $metadata['is_public'] = confirm(
            label: 'Make this setting public?',
            default: false,
            hint: 'Public settings can be accessed in frontend/API without authentication'
        );

        $orderInput = text(
            label: 'Display Order (optional)',
            placeholder: 'e.g., 1, 10, 100',
            hint: 'Used to sort settings in lists (lower numbers appear first)'
        );

        if ($orderInput && is_numeric($orderInput)) {
            $metadata['order'] = (int) $orderInput;
        }

        return $metadata;
    }

    /**
     * Select or enter group.
     */
    protected function selectGroup(): ?string
    {
        $availableGroups = config('settings.groups', []);

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
            return text(
                label: 'Custom Group Name',
                placeholder: 'e.g., api, email, features',
                required: false
            );
        }

        return $selectedGroup !== 'none' ? $selectedGroup : null;
    }

    /**
     * Generate default label from key.
     */
    protected function generateDefaultLabel(string $key): string
    {
        $parts = explode('.', $key);
        $lastPart = end($parts);

        return ucwords(str_replace(['_', '-'], ' ', $lastPart));
    }

    /**
     * Display complete summary.
     */
    protected function displayCompleteSummary(string $key, mixed $value, ?string $group, bool $encrypted, array $metadata): void
    {
        $type = gettype($value);
        if (is_array($value)) {
            $type = 'array ('.count($value).' items)';
        }

        $this->newLine();
        $this->components->info('📋 Setting Summary');
        $this->newLine();

        $this->components->twoColumnDetail('<fg=gray>Key</>', $key);
        $this->components->twoColumnDetail('<fg=gray>Value</>', $encrypted ? '<fg=yellow>***encrypted***</>' : json_encode($value));
        $this->components->twoColumnDetail('<fg=gray>Type</>', $type);
        $this->components->twoColumnDetail('<fg=gray>Group</>', $group ?? '<fg=gray>(none)</>');

        if (! empty($metadata)) {
            if (isset($metadata['label'])) {
                $labelDisplay = is_array($metadata['label']) ? json_encode($metadata['label']) : $metadata['label'];
                $this->components->twoColumnDetail('<fg=gray>Label</>', $labelDisplay);
            }

            if (isset($metadata['description'])) {
                $this->components->twoColumnDetail('<fg=gray>Description</>', substr($metadata['description'], 0, 60).'...');
            }

            if (isset($metadata['input_type'])) {
                $this->components->twoColumnDetail('<fg=gray>Input Type</>', $metadata['input_type']);
            }

            if (isset($metadata['validation_rules'])) {
                $this->components->twoColumnDetail('<fg=gray>Validation</>', implode(', ', $metadata['validation_rules']));
            }

            if (isset($metadata['is_public'])) {
                $this->components->twoColumnDetail('<fg=gray>Public</>', $metadata['is_public'] ? '<fg=green>Yes</>' : '<fg=gray>No</>');
            }

            if (isset($metadata['order'])) {
                $this->components->twoColumnDetail('<fg=gray>Order</>', (string) $metadata['order']);
            }
        }

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
