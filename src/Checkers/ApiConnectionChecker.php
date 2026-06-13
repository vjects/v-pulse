<?php

namespace Vjects\Pulse\Checkers;

use Illuminate\Support\Facades\Http;

class ApiConnectionChecker extends BaseChecker
{
    public function getName(): string
    {
        return 'API Ecosystem Connection';
    }

    public function getDescription(): string
    {
        return 'Checks if the Master server can ping the API Ecosystem server.';
    }

    public function isApplicable(array $settings): bool
    {
        // Only run if the system is in ecosystem mode
        return isset($settings['mode']) && $settings['mode'] === 'ecosystem';
    }

    public function run(): array
    {
        $apiUrl = config('app.api_url'); // Assume we have this in config

        if (!$apiUrl) {
            return [
                'success' => false,
                'message' => 'API URL is not defined in .env (APP_API_URL).',
            ];
        }

        try {
            $response = Http::timeout(3)->get($apiUrl . '/api/ping');
            
            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'Master successfully connected to API Ecosystem.',
                ];
            }

            return [
                'success' => false,
                'message' => 'API server returned an error: ' . $response->status(),
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Network request failed: Connection Refused or Timeout.',
            ];
        }
    }

    public function getFixActionName(): ?string
    {
        // Since fixing this requires .env edits or starting the API server, 
        // we might not have a simple Artisan fix, but we can provide instructions.
        return null; 
    }
}
