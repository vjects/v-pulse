<?php

namespace Vjects\Pulse\Checkers;

use Illuminate\Support\Facades\Redis;

class RedisChecker extends BaseChecker
{
    public function getName(): string
    {
        return $this->tr('redis_name');
    }

    public function getDescription(): string
    {
        return $this->tr('redis_desc');
    }

    public function isApplicable(array $settings): bool
    {
        return true;
    }

    public function getFixActionName(): ?string
    {
        return 'فعال‌سازی سیستم جایگزین (Fallback)';
    }

    public function performFix(): void
    {
        $path = base_path('.env');
        if (file_exists($path)) {
            $content = file_get_contents($path);
            $content = preg_replace('/^CACHE_DRIVER=redis/m', 'CACHE_DRIVER=database', $content);
            $content = preg_replace('/^QUEUE_CONNECTION=redis/m', 'QUEUE_CONNECTION=database', $content);
            $content = preg_replace('/^SESSION_DRIVER=redis/m', 'SESSION_DRIVER=database', $content);
            file_put_contents($path, $content);
            
            \Illuminate\Support\Facades\Artisan::call('config:clear');
            \Illuminate\Support\Facades\Artisan::call('cache:clear');
        }
    }

    public function run(): array
    {
        try {
            $driver = config('database.redis.client');
            
            // Only check if redis is actually configured or being used by queue/cache/session
            $isUsed = config('cache.default') === 'redis' || 
                      config('queue.default') === 'redis' || 
                      config('session.driver') === 'redis' ||
                      !empty(config('database.redis.default.host'));

            if (!$isUsed) {
                return [
                    'success' => true,
                    'message' => $this->tr('redis_disabled', ['driver' => config('cache.default')]),
                ];
            }

            // Attempt to ping Redis
            $response = Redis::connection()->ping();
            
            if (!$response) {
                throw new \Exception('Ping returned false');
            }

            return [
                'success' => true,
                'message' => $this->tr('redis_ok')
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $this->tr('redis_fail', ['error' => $e->getMessage()])
            ];
        }
    }
}
