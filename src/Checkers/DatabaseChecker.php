<?php

namespace Vjects\Pulse\Checkers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

class DatabaseChecker extends BaseChecker
{
    public function getName(): string
    {
        return 'Core Database Health';
    }

    public function getDescription(): string
    {
        return 'Checks if the main database is connected and core tables exist.';
    }

    public function run(): array
    {
        try {
            DB::connection()->getPdo();
            
            // Check if migrations table exists
            if (!Schema::hasTable('migrations')) {
                return [
                    'success' => false,
                    'message' => 'Database is connected, but migrations have not been run.',
                ];
            }

            return [
                'success' => true,
                'message' => 'Database connected and migrations exist.',
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to connect to database: ' . $e->getMessage(),
            ];
        }
    }

    public function getFixActionName(): ?string
    {
        try {
            DB::connection()->getPdo();
            if (!Schema::hasTable('migrations')) {
                return 'Run Migrations';
            }
        } catch (\Exception $e) {
            // If DB is disconnected, Artisan migrate won't fix it (needs .env fix)
        }
        return null;
    }

    public function executeFix(): void
    {
        Artisan::call('migrate', ['--force' => true]);
    }
}
