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
        try {
            // Simulated API Check to vjects ecosystem master node
            $response = Http::timeout(3)->get('https://api.vjects.com/health');
            
            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => $this->tr('api_ok')
                ];
            }
            
            throw new \Exception('Status code: ' . $response->status());
        } catch (\Exception $e) {
            $msg = $this->tr('api_fail', ['error' => $e->getMessage()]);
            
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
    }

    public function getFixActionName(): ?string
    {
        return $this->tr('api_fix');
    }
}
