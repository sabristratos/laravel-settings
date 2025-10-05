<?php

namespace Strata\Settings\Observers;

use Strata\Settings\Events\UserSettingCreated;
use Strata\Settings\Events\UserSettingDeleted;
use Strata\Settings\Events\UserSettingUpdated;
use Strata\Settings\Models\UserSetting;

class UserSettingObserver
{
    /**
     * Handle the UserSetting "created" event.
     */
    public function created(UserSetting $userSetting): void
    {
        event(new UserSettingCreated($userSetting));
    }

    /**
     * Handle the UserSetting "updated" event.
     */
    public function updated(UserSetting $userSetting): void
    {
        $oldValue = $userSetting->getOriginal('value');

        event(new UserSettingUpdated($userSetting, $oldValue));
    }

    /**
     * Handle the UserSetting "deleted" event.
     */
    public function deleted(UserSetting $userSetting): void
    {
        event(new UserSettingDeleted($userSetting));
    }
}
