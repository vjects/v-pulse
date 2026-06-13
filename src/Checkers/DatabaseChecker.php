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
            \Illuminate\Support\Facades\Artisan::call('db:seed', ['--force' => true]);
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
