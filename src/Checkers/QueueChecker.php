<?php

namespace Vjects\Pulse\Checkers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;

class QueueChecker extends BaseChecker
{
    protected bool $hasFailedJobs = false;

    public function getName(): string
    {
        return $this->tr('queue_name');
    }

    public function getDescription(): string
    {
        return $this->tr('queue_desc');
    }

    public function isApplicable(array $settings): bool
    {
        return true;
    }

    public function getFixActionName(): ?string
    {
        return $this->hasFailedJobs ? $this->tr('queue_fix') : null;
    }

    public function performFix(): void
    {
        if ($this->hasFailedJobs) {
            Artisan::call('queue:retry', ['all' => true]);
        }
    }

    public function run(): array
    {
        try {
            // Ensure DB connection is active first
            DB::connection()->getPdo();

            $failedJobsCount = 0;
            $pendingJobsCount = 0;

            if (Schema::hasTable('failed_jobs')) {
                $failedJobsCount = DB::table('failed_jobs')->count();
            }

            if (Schema::hasTable('jobs')) {
                $pendingJobsCount = DB::table('jobs')->count();
            }

            if ($failedJobsCount > 0) {
                $this->hasFailedJobs = true;
                return [
                    'success' => false,
                    'message' => $this->tr('queue_failed', ['count' => $failedJobsCount]),
                ];
            }

            if ($pendingJobsCount > 50) {
                return [
                    'success' => false,
                    'message' => $this->tr('queue_backed_up', ['count' => $pendingJobsCount]),
                ];
            }

            return [
                'success' => true,
                'message' => $this->tr('queue_ok')
            ];
            
        } catch (\Exception $e) {
            // If DB is down, we just fail gracefully, let DatabaseChecker handle DB down error
            return [
                'success' => false,
                'message' => $this->tr('db_fail', ['error' => $e->getMessage()])
            ];
        }
    }
}
