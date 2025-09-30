<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Database\Seeders\UserRolePermissionSeeder;

class AppInitCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:init {--initdb} {--seed} {--all}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initializes the application, runs migrations, seeds, and sets up base roles.';

    const DB_SEED = 'db:seed';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("* {$this->signature}");

        if ($this->option('initdb') || $this->option('all')) {
            $this->initPostgresDatabase();
        }

        return Command::SUCCESS;
    }

    private function initPostgresDatabase(): void
    {
        $this->runQueries([
            'CREATE SCHEMA IF NOT EXISTS "public"',
            // 'DROP SCHEMA IF EXISTS "be" CASCADE',
            // 'CREATE SCHEMA IF NOT EXISTS "be"',
            // 'DROP SCHEMA IF EXISTS "etl" CASCADE',
            // 'CREATE SCHEMA IF NOT EXISTS "etl"',
            'DROP EXTENSION IF EXISTS "uuid-ossp"',
            'CREATE EXTENSION IF NOT EXISTS "uuid-ossp" WITH schema "public"',
        ]);

        $this->call('migrate:refresh');

        $this->runRequiredSeeders();

        if ($this->option('seed')) {
            $this->runFakeDataSeeders();
        }
    }

    private function runQueries(array $queries): void
    {
        foreach ($queries as $query) {
            $result = DB::statement($query);
            $this->info($query . " ($result)");
        }
    }

    private function runRequiredSeeders(): void
    {
        $this->call(self::DB_SEED, ['class' => UserRolePermissionSeeder::class]);
        // $this->call(self::DB_SEED);
    }

    private function runFakeDataSeeders(): void
    {
        // $this->call(self::DB_SEED, ['class' => SocialNetworkSeeder::class]);
    }
}
