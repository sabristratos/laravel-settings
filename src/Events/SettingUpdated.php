<?php

namespace Strata\Settings\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Strata\Settings\Models\Setting;

class SettingUpdated
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public Setting $setting,
        public mixed $oldValue = null
    ) {}
}
