<?php

namespace Strata\Settings\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;

class UserSetting extends Model
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
        'user_id',
        'key',
        'label',
        'description',
        'value',
        'type',
        'encrypted',
        'validation_rules',
        'options',
        'input_type',
        'order',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'label' => 'array',
            'description' => 'array',
            'encrypted' => 'boolean',
            'validation_rules' => 'array',
            'options' => 'array',
            'order' => 'integer',
        ];
    }

    /**
     * Create a new instance of the model.
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->table = config('settings.tables.user_settings', 'user_settings');
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return \Strata\Settings\Database\Factories\UserSettingFactory::new();
    }

    /**
     * Get the user that owns the setting.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model', 'App\\Models\\User'));
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
     * Scope a query to find a setting by key.
     */
    public function scopeKey($query, string $key)
    {
        return $query->where('key', $key);
    }

    /**
     * Scope a query to find settings for a specific user.
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope a query to order settings by the order column.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
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
