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
