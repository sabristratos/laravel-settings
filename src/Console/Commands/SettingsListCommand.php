<?php

namespace Strata\Settings\Console\Commands;

use Illuminate\Console\Command;
use Stratos\Settings\Models\Setting;

class SettingsListCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'settings:list
                            {--group= : Filter by group}
                            {--public : Show only public settings}';

    /**
     * The console command description.
     */
    protected $description = 'List all settings';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $query = Setting::query();

        if ($group = $this->option('group')) {
            $query->group($group);
        }

        if ($this->option('public')) {
            $query->where('is_public', true);
        }

        $settings = $query->ordered()->get();

        if ($settings->isEmpty()) {
            $this->info('No settings found.');

            return self::SUCCESS;
        }

        $headers = ['Key', 'Value', 'Group', 'Type', 'Encrypted', 'Public'];
        $rows = [];

        foreach ($settings as $setting) {
            $rows[] = [
                $setting->key,
                $setting->encrypted ? '***encrypted***' : $this->formatValue($setting->getCastedValue()),
                $setting->group ?? '-',
                $setting->type,
                $setting->encrypted ? 'Yes' : 'No',
                $setting->is_public ? 'Yes' : 'No',
            ];
        }

        $this->table($headers, $rows);

        $this->info("Total: {$settings->count()} setting(s)");

        return self::SUCCESS;
    }

    /**
     * Format the value for display.
     */
    protected function formatValue(mixed $value): string
    {
        if (is_array($value)) {
            return json_encode($value);
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        return (string) $value;
    }
}
