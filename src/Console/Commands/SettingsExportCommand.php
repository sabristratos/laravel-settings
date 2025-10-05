<?php

namespace Strata\Settings\Console\Commands;

use Illuminate\Console\Command;
use Strata\Settings\Facades\Settings;

class SettingsExportCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'settings:export
                            {--format=json : Export format (json or yaml)}
                            {--group= : Export specific group only}
                            {--file= : Output file path}
                            {--no-metadata : Exclude metadata}
                            {--include-encrypted : Include encrypted settings}';

    /**
     * The console command description.
     */
    protected $description = 'Export settings to JSON or YAML';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $format = $this->option('format');
        $file = $this->option('file');
        $group = $this->option('group');
        $includeMetadata = ! $this->option('no-metadata');
        $includeEncrypted = $this->option('include-encrypted');

        if (! in_array($format, ['json', 'yaml'])) {
            $this->error('Invalid format. Use json or yaml.');

            return self::FAILURE;
        }

        try {
            $options = [
                'include_metadata' => $includeMetadata,
                'include_encrypted' => $includeEncrypted,
            ];

            if ($group) {
                $options['group'] = $group;
            }

            $exported = Settings::export($format, $options);

            if ($file) {
                file_put_contents($file, $exported);
                $this->info("Settings exported successfully to: {$file}");
            } else {
                $this->line($exported);
            }

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Export failed: {$e->getMessage()}");

            return self::FAILURE;
        }
    }
}
