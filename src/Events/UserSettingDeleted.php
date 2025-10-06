<?php

namespace Stratos\Settings\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Stratos\Settings\Models\UserSetting;

class UserSettingDeleted
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public UserSetting $userSetting
    ) {}
}
