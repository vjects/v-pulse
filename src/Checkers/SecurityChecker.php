<?php

namespace Vjects\Pulse\Checkers;

use Illuminate\Support\Facades\Route;

class SecurityChecker extends BaseChecker
{
    public function getName(): string
    {
        return 'Application Security Baseline';
    }

    public function getDescription(): string
    {
        return 'Checks basic security configurations like APP_DEBUG and API Rate Limiting.';
    }

    public function isApplicable(array $settings): bool
    {
        return in_array('security', $settings['modules'] ?? []);
    }

    public function run(): array
    {
        $issues = [];

        // 1. Check APP_DEBUG
        if (config('app.debug') === true && app()->environment('production')) {
            $issues[] = 'APP_DEBUG is TRUE in production environment (High Risk).';
        }

        // 2. Check APP_ENV
        if (app()->environment('local')) {
            $issues[] = 'APP_ENV is set to "local". Ensure it is "production" on live servers.';
        }

        // 3. Check Route Rate Limiting (Basic API Check)
        // Check if api middleware group has throttle
        $apiMiddleware = config('route.middlewareGroups.api') ?? [];
        $hasThrottle = false;
        
        if (is_array($apiMiddleware)) {
            foreach ($apiMiddleware as $mw) {
                if (is_string($mw) && str_contains($mw, 'throttle')) {
                    $hasThrottle = true;
                    break;
                }
            }
        }
        
        // In modern laravel, it might be in RouteServiceProvider or bootstrap/app.php
        // Let's do a simple fallback check
        if (!$hasThrottle && !Route::has('api/*')) {
            // It's a heuristic. If we find no throttle, we warn.
            $issues[] = 'API Rate Limiting might not be configured properly (throttle middleware missing in basic checks).';
        }

        if (empty($issues)) {
            return [
                'success' => true,
                'message' => 'Basic security configurations look solid.',
            ];
        }

        return [
            'success' => false,
            'message' => 'Security Issues Detected: ' . implode(' | ', $issues),
        ];
    }

    public function getFixActionName(): ?string
    {
        if (config('app.debug') === true) {
            return 'Disable Debug Mode';
        }
        return null;
    }

    public function executeFix(): void
    {
        // Fixing APP_DEBUG programmatically is dangerous as it requires rewriting .env
        // But for demonstration:
        $path = base_path('.env');
        if (file_exists($path)) {
            $content = file_get_contents($path);
            $content = preg_replace('/^APP_DEBUG=(.*)$/m', 'APP_DEBUG=false', $content);
            file_put_contents($path, $content);
        }
    }
}
