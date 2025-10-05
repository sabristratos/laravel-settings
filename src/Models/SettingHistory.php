<?php

namespace Strata\Settings\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SettingHistory extends Model
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'setting_key',
        'old_value',
        'new_value',
        'old_type',
        'new_type',
        'user_id',
        'ip_address',
        'user_agent',
        'action',
    ];

    /**
     * Get the table associated with the model.
     */
    public function getTable(): string
    {
        return config('settings.tables.setting_histories', 'setting_histories');
    }

    /**
     * Get the user that made the change.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model', 'App\\Models\\User'));
    }

    /**
     * Get the casted old value.
     */
    public function getOldValueAttribute($value): mixed
    {
        if ($value === null) {
            return null;
        }

        return $this->castValue($value, $this->old_type);
    }

    /**
     * Get the casted new value.
     */
    public function getNewValueAttribute($value): mixed
    {
        if ($value === null) {
            return null;
        }

        return $this->castValue($value, $this->new_type);
    }

    /**
     * Cast value to appropriate type.
     */
    protected function castValue(mixed $value, ?string $type): mixed
    {
        return match ($type) {
            'integer' => (int) $value,
            'float', 'double' => (float) $value,
            'boolean' => (bool) $value,
            'array', 'object' => json_decode($value, true),
            default => $value,
        };
    }

    /**
     * Scope to filter by setting key.
     */
    public function scopeForKey($query, string $key)
    {
        return $query->where('setting_key', $key);
    }

    /**
     * Scope to filter by user.
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to filter by action.
     */
    public function scopeAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope to order by most recent.
     */
    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }
}
