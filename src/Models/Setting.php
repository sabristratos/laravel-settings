<?php

namespace Stratos\Settings\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class Setting extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'group',
        'key',
        'label',
        'description',
        'value',
        'type',
        'encrypted',
        'validation_rules',
        'options',
        'input_type',
        'is_public',
        'order',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'label' => 'array',
            'description' => 'array',
            'encrypted' => 'boolean',
            'validation_rules' => 'array',
            'options' => 'array',
            'is_public' => 'boolean',
            'order' => 'integer',
        ];
    }

    /**
     * Create a new instance of the model.
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->table = config('settings.tables.settings', 'settings');
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return \Stratos\Settings\Database\Factories\SettingFactory::new();
    }

    /**
     * Get the setting value with proper type casting.
     */
    public function getCastedValue(): mixed
    {
        $value = $this->encrypted ? $this->getDecryptedValue() : $this->value;

        return match ($this->type) {
            'int', 'integer' => (int) $value,
            'bool', 'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'float', 'double' => (float) $value,
            'array', 'json' => is_string($value) ? json_decode($value, true) : $value,
            default => $value,
        };
    }

    /**
     * Set the setting value with type information.
     */
    public function setCastedValue(mixed $value): void
    {
        $this->type = $this->determineType($value);

        if (in_array($this->type, ['array', 'json'])) {
            $value = json_encode($value);
        }

        $this->value = $value;
    }

    /**
     * Get the decrypted value.
     */
    protected function getDecryptedValue(): string
    {
        try {
            return Crypt::decryptString($this->value);
        } catch (\Exception $e) {
            return $this->value;
        }
    }

    /**
     * Determine the type of the value.
     */
    protected function determineType(mixed $value): string
    {
        return match (true) {
            is_int($value) => 'int',
            is_bool($value) => 'bool',
            is_float($value) => 'float',
            is_array($value) => 'array',
            default => 'string',
        };
    }

    /**
     * Scope a query to only include settings from a specific group.
     */
    public function scopeGroup($query, ?string $group)
    {
        return $query->where('group', $group);
    }

    /**
     * Scope a query to find a setting by key.
     */
    public function scopeKey($query, string $key)
    {
        return $query->where('key', $key);
    }

    /**
     * Scope a query to only include public settings.
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * Scope a query to order settings by the order column.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    /**
     * Get the translated label for the current locale.
     */
    public function getTranslatedLabel(?string $locale = null): ?string
    {
        $locale = $locale ?? app()->getLocale();
        $defaultLocale = config('settings.default_locale', 'en');

        if (! $this->label) {
            return null;
        }

        return $this->label[$locale] ?? $this->label[$defaultLocale] ?? null;
    }

    /**
     * Get the translated description for the current locale.
     */
    public function getTranslatedDescription(?string $locale = null): ?string
    {
        $locale = $locale ?? app()->getLocale();
        $defaultLocale = config('settings.default_locale', 'en');

        if (! $this->description) {
            return null;
        }

        return $this->description[$locale] ?? $this->description[$defaultLocale] ?? null;
    }

    /**
     * Get the translated options for the current locale.
     */
    public function getTranslatedOptions(?string $locale = null): ?array
    {
        $locale = $locale ?? app()->getLocale();
        $defaultLocale = config('settings.default_locale', 'en');

        if (! $this->options) {
            return null;
        }

        return $this->options[$locale] ?? $this->options[$defaultLocale] ?? null;
    }

    /**
     * Validate a value against the stored validation rules.
     */
    public function validate(mixed $value): bool
    {
        if (! $this->validation_rules) {
            return true;
        }

        $validator = \Illuminate\Support\Facades\Validator::make(
            ['value' => $value],
            ['value' => $this->validation_rules]
        );

        return $validator->passes();
    }

    /**
     * Get validation errors for a value.
     */
    public function getValidationErrors(mixed $value): array
    {
        if (! $this->validation_rules) {
            return [];
        }

        $validator = \Illuminate\Support\Facades\Validator::make(
            ['value' => $value],
            ['value' => $this->validation_rules]
        );

        if ($validator->passes()) {
            return [];
        }

        return $validator->errors()->get('value');
    }
}
