<?php

namespace Vjects\Pulse;

use Illuminate\Support\Facades\Storage;

class PulseManager
{
    protected array $checkers = [];

    /**
     * Get settings from isolated JSON file.
     */
    public function getSettings(): array
    {
        $path = storage_path('app/vpulse.json');
        
        if (!file_exists($path)) {
            return [
                'mode' => 'monolith', // or 'ecosystem'
                'modules' => [],
            ];
        }

        return json_decode(file_get_contents($path), true) ?? [];
    }

    /**
     * Save settings to isolated JSON file.
     */
    public function saveSettings(array $settings): void
    {
        $dir = storage_path('app');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $path = $dir . '/vpulse.json';
        file_put_contents($path, json_encode($settings, JSON_PRETTY_PRINT));
    }

    public function getAiHistory(): array
    {
        $path = storage_path('app/vpulse_ai.json');
        return file_exists($path) ? json_decode(file_get_contents($path), true) ?? [] : [];
    }

    public function saveAiAnalysis(array $data): void
    {
        $dir = storage_path('app');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $path = $dir . '/vpulse_ai.json';
        $history = $this->getAiHistory();
        
        $data['id'] = uniqid('ai_');
        $data['is_read'] = false;
        
        array_unshift($history, $data); // Add to top
        $history = array_slice($history, 0, 15); // Keep last 15 analyses
        
        file_put_contents($path, json_encode($history, JSON_PRETTY_PRINT));
    }

    public function markAiHistoryAsRead(string $id): void
    {
        $path = storage_path('app/vpulse_ai.json');
        if (!file_exists($path)) return;
        
        $history = json_decode(file_get_contents($path), true) ?? [];
        $modified = false;
        
        foreach ($history as &$item) {
            if (($item['id'] ?? '') === $id) {
                $item['is_read'] = true;
                $modified = true;
                break;
            }
        }
        
        if ($modified) {
            file_put_contents($path, json_encode($history, JSON_PRETTY_PRINT));
        }
    }

    /**
     * Register a new Checker.
     */
    public function registerChecker(string $checkerClass): void
    {
        $this->checkers[] = $checkerClass;
    }

    /**
     * Get all registered checkers.
     */
    public function getCheckers(): array
    {
        return $this->checkers;
    }

    public function runChecks(): array
    {
        $results = [];
        $settings = $this->getSettings();

        foreach ($this->checkers as $checkerClass) {
            /** @var \Vjects\Pulse\Checkers\CheckerInterface $checker */
            $checker = app($checkerClass);

            // Skip if the checker is not applicable based on settings
            if (!$checker->isApplicable($settings)) {
                continue;
            }

            try {
                $status = $checker->run();
                if (!$status['success']) {
                    $this->logError($checker->getName() . ' | ' . $status['message']);
                }
                
                $results[] = [
                    'name' => $checker->getName(),
                    'description' => $checker->getDescription(),
                    'status' => $status['success'] ? 'success' : 'danger',
                    'message' => $status['message'],
                    'action' => $checker->getFixActionName(),
                    'instance' => $checker,
                ];
            } catch (\Throwable $e) {
                $this->logError($checker->getName() . ' | Exception: ' . $e->getMessage());
                $results[] = [
                    'name' => $checker->getName(),
                    'description' => $checker->getDescription(),
                    'status' => 'danger',
                    'message' => 'Exception: ' . $e->getMessage(),
                    'action' => null,
                    'instance' => $checker,
                ];
            }
        }

        return $results;
    }

    protected function logError(string $message): void
    {
        $logPath = storage_path('logs/vpulse.log');
        $date = date('Y-m-d H:i:s');
        $line = "[$date] $message\n";
        file_put_contents($logPath, $line, FILE_APPEND);
    }
}
