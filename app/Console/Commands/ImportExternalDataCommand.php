<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Infrastructure\Integrations\PushTech\Repositories\CampaignEtlRepository;

class ImportExternalDataCommand extends Command
{
    protected $signature = 'app:import-external-data';
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(
        CampaignEtlRepository $etl,
    ) {
        $this->info('Starting PushTech campaigns sync...');

        $count = 0;
        $list = [];

        foreach ($etl->getAll() as $campaignData) {
            $list[] = $campaignData;
            $count++;

            if ($count % 100 === 0) {
                $this->info("Processed {$count} campaigns...");
            }
        }

        dd($list);

        $this->info("Finished. Total campaigns synced: {$count}");
    }
}
