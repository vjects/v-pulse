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

    /**
     * Run all checkers safely.
     */
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
                $results[] = [
                    'name' => $checker->getName(),
                    'description' => $checker->getDescription(),
                    'status' => $status['success'] ? 'success' : 'danger',
                    'message' => $status['message'],
                    'action' => $checker->getFixActionName(),
                    'instance' => $checker,
                ];
            } catch (\Throwable $e) {
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
}
