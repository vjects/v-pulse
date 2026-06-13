<?php

namespace Vjects\Pulse\Checkers;

class SecurityChecker extends BaseChecker
{
    public function getName(): string
    {
        return $this->tr('sec_name');
    }

    public function getDescription(): string
    {
        return $this->tr('sec_desc');
    }

    public function isApplicable(array $settings): bool
    {
        return in_array('security', $settings['modules'] ?? []);
    }

    public function run(): array
    {
        $issues = [];
        
        if (config('app.debug')) {
            $issues[] = 'APP_DEBUG is true';
        }
        
        if (config('app.env') === 'local') {
            $issues[] = 'APP_ENV is set to "local"';
        }

        if (!empty($issues)) {
            // Environment Awareness
            if ($this->isLocal()) {
                // Return success in local even if debug is true, but add a note
                return [
                    'success' => true,
                    'message' => $this->tr('sec_fail', ['issues' => implode(' | ', $issues)]) . $this->tr('sec_local_note')
                ];
            }
            
            return [
                'success' => false,
                'message' => $this->tr('sec_fail', ['issues' => implode(' | ', $issues)])
            ];
        }

        return [
            'success' => true,
            'message' => $this->tr('sec_ok')
        ];
    }

    public function getFixActionName(): ?string
    {
        return $this->tr('sec_fix');
    }

    public function executeFix(): void
    {
        // Simple fix: touch the .env file to set APP_DEBUG=false
        // For a real package, modifying .env dynamically is risky, but we do it for demo
        $path = base_path('.env');
        if (file_exists($path)) {
            $content = file_get_contents($path);
            $content = preg_replace('/^APP_DEBUG=true/m', 'APP_DEBUG=false', $content);
            file_put_contents($path, $content);
        }
    }
}
