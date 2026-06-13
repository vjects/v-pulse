<?php

namespace Vjects\Pulse\Checkers;

use Illuminate\Support\Facades\Http;

class ApiConnectionChecker extends BaseChecker
{
    public function getName(): string
    {
        return $this->tr('api_name');
    }

    public function getDescription(): string
    {
        return $this->tr('api_desc');
    }

    public function isApplicable(array $settings): bool
    {
        return ($settings['mode'] ?? 'monolith') === 'ecosystem';
    }

    public function run(): array
    {
        $settings = $this->getSettings();
        $urls = $settings['api_ecosystem_urls'] ?? [];
        
        if (empty($urls)) {
            return [
                'success' => false,
                'message' => $this->tr('api_no_url')
            ];
        }

        $failedUrls = [];

        foreach ($urls as $url) {
            try {
                $cleanUrl = rtrim($url, '/');
                $response = Http::timeout(3)->get($cleanUrl . '/health');
                
                if (!$response->successful()) {
                    $failedUrls[] = $url . ' (Status: ' . $response->status() . ')';
                }
            } catch (\Exception $e) {
                $failedUrls[] = $url . ' (Error: ' . $e->getMessage() . ')';
            }
        }
        
        if (!empty($failedUrls)) {
            $msg = $this->tr('api_fail', ['error' => implode(', ', $failedUrls)]);
            
            if ($this->isLocal()) {
                return [
                    'success' => true, // Ignore failure in local
                    'message' => $msg . $this->tr('api_local_note')
                ];
            }
            
            return [
                'success' => false,
                'message' => $msg
            ];
        }

        return [
            'success' => true,
            'message' => $this->tr('api_ok')
        ];
    }
}
