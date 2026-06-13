<?php

namespace Vjects\Pulse\Checkers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Artisan;

class CacheChecker extends BaseChecker
{
    protected bool $needsOptimization = false;

    public function getName(): string
    {
        return $this->tr('cache_name');
    }

    public function getDescription(): string
    {
        return $this->tr('cache_desc');
    }

    public function isApplicable(array $settings): bool
    {
        return true;
    }

    public function getFixActionName(): ?string
    {
        return $this->needsOptimization ? $this->tr('cache_fix') : null;
    }

    public function performFix(): void
    {
        if ($this->needsOptimization) {
            Artisan::call('optimize:clear');
            Artisan::call('optimize');
        }
    }

    public function run(): array
    {
        try {
            $driver = config('cache.default');
            
            // Test R/W operations
            $testKey = 'vpulse_cache_test_' . uniqid();
            Cache::put($testKey, 'ok', 10);
            $val = Cache::get($testKey);
            Cache::forget($testKey);
            
            if ($val !== 'ok') {
                throw new \Exception('Read/Write operation failed. Cache returned invalid data.');
            }

            // In production, file/database cache drivers are slow
            if (!$this->isLocal() && in_array($driver, ['file', 'database'])) {
                $this->needsOptimization = true;
                return [
                    'success' => false,
                    'message' => $this->tr('cache_warn', ['driver' => $driver]),
                ];
            }

            return [
                'success' => true,
                'message' => $this->tr('cache_ok', ['driver' => $driver])
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $this->tr('cache_fail', ['error' => $e->getMessage()])
            ];
        }
    }
}
