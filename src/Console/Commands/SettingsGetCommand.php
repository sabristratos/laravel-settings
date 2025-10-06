<?php

namespace Stratos\Settings\Console\Commands;

use Illuminate\Console\Command;
use Stratos\Settings\Facades\Settings;

class SettingsGetCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'settings:get {key : The setting key} {--default= : Default value if setting does not exist}';

    /**
     * The console command description.
     */
    protected $description = 'Get a setting value';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $key = $this->argument('key');
        $default = $this->option('default');

        $value = Settings::get($key, $default);

        if ($value === null && $default === null) {
            $this->error("Setting '{$key}' not found.");

            return self::FAILURE;
        }

        $this->info("Setting: {$key}");
        $this->line('Value: '.json_encode($value, JSON_PRETTY_PRINT));

        return self::SUCCESS;
    }
}
