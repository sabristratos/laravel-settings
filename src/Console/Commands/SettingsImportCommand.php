<?php

namespace Strata\Settings\Console\Commands;

use Illuminate\Console\Command;
use Stratos\Settings\Facades\Settings;

class SettingsImportCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'settings:import
                            {file : The file to import from}
                            {--format=json : Import format (json or yaml)}
                            {--force : Skip confirmation}';

    /**
     * The console command description.
     */
    protected $description = 'Import settings from JSON or YAML file';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $file = $this->argument('file');
        $format = $this->option('format');
        $force = $this->option('force');

        if (! file_exists($file)) {
            $this->error("File not found: {$file}");

            return self::FAILURE;
        }

        if (! in_array($format, ['json', 'yaml'])) {
            $this->error('Invalid format. Use json or yaml.');

            return self::FAILURE;
        }

        if (! $force && ! $this->confirm('This will override existing settings. Continue?')) {
            $this->info('Import cancelled.');

            return self::SUCCESS;
        }

        try {
            $data = file_get_contents($file);

            Settings::import($data, $format);

            $this->info('Settings imported successfully.');

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Import failed: {$e->getMessage()}");

            return self::FAILURE;
        }
    }
}
