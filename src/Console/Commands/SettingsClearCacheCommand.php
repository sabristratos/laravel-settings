<?php

namespace Strata\Settings\Console\Commands;

use Illuminate\Console\Command;
use Stratos\Settings\Facades\Settings;

class SettingsClearCacheCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'settings:clear-cache';

    /**
     * The console command description.
     */
    protected $description = 'Clear all settings cache';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (! config('settings.cache.enabled', true)) {
            $this->info('Settings cache is disabled.');

            return self::SUCCESS;
        }

        Settings::flush();

        $this->info('Settings cache cleared successfully.');

        return self::SUCCESS;
    }
}
