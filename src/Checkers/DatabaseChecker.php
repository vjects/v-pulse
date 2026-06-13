<?php

namespace Vjects\Pulse\Checkers;

use Illuminate\Support\Facades\DB;

class DatabaseChecker extends BaseChecker
{
    protected bool $needsSeed = false;

    public function getName(): string
    {
        return $this->tr('db_name');
    }

    public function getDescription(): string
    {
        return $this->tr('db_desc');
    }

    public function isApplicable(array $settings): bool
    {
        return true; // Always applicable
    }
    
    public function getFixActionName(): ?string
    {
        return $this->needsSeed ? $this->tr('db_missing_seed_action') : null;
    }

    public function performFix(): void
    {
        if ($this->needsSeed) {
            if (class_exists(\Database\Seeders\PaymentMethodSeeder::class)) {
                \Illuminate\Support\Facades\Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\PaymentMethodSeeder', '--force' => true]);
            }
            if (class_exists(\Database\Seeders\DefaultPageSeeder::class)) {
                \Illuminate\Support\Facades\Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\DefaultPageSeeder', '--force' => true]);
            }
            if (class_exists(\Database\Seeders\StorageNodeSeeder::class)) {
                \Illuminate\Support\Facades\Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\StorageNodeSeeder', '--force' => true]);
            }
        }
    }

    public function run(): array
    {
        try {
            DB::connection()->getPdo();
            
            $missing = [];
            if (class_exists(\App\Models\Admin::class) && \App\Models\Admin::count() === 0) {
                $missing[] = 'Admin';
            }
            if (class_exists(\App\Models\PaymentMethod::class) && \App\Models\PaymentMethod::count() === 0) {
                $missing[] = 'Payment Methods';
            }
            if (class_exists(\App\Models\DefaultPage::class) && \App\Models\DefaultPage::count() === 0) {
                $missing[] = 'Default Pages';
            }
            if (class_exists(\App\Models\StorageNode::class) && \App\Models\StorageNode::count() === 0) {
                $missing[] = 'Storage Nodes';
            }
            
            if (!empty($missing)) {
                $this->needsSeed = true;
                return [
                    'success' => false,
                    'message' => $this->tr('db_missing_seed', ['tables' => implode(', ', $missing)])
                ];
            }
            
            return [
                'success' => true,
                'message' => $this->tr('db_ok')
            ];
        } catch (\Exception $e) {
            $msg = $this->tr('db_fail', ['error' => $e->getMessage()]);
            if ($this->isLocal()) {
                $msg .= $this->tr('db_local_note');
            }
            return [
                'success' => false,
                'message' => $msg
            ];
        }
    }
}
