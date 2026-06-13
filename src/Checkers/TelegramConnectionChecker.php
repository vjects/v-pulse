<?php

namespace Vjects\Pulse\Checkers;

use Illuminate\Support\Facades\Http;

class TelegramConnectionChecker extends BaseChecker
{
    public function getName(): string
    {
        return $this->tr('tg_name');
    }

    public function getDescription(): string
    {
        return $this->tr('tg_desc');
    }

    public function isApplicable(array $settings): bool
    {
        return in_array('telegram', $settings['modules'] ?? []);
    }

    public function run(): array
    {
        $settings = $this->getSettings();
        $token = $settings['telegram_bot_token'] ?? null;

        if (!$token) {
            return [
                'success' => false,
                'message' => 'Telegram token not configured.'
            ];
        }

        try {
            $options = [];
            if (($settings['use_telegram_proxy'] ?? false) && !empty($settings['proxy_server'])) {
                $options['proxy'] = $settings['proxy_server'] . ':' . ($settings['proxy_port'] ?? 80);
            }

            $response = Http::timeout(5)->withOptions($options)->get("https://api.telegram.org/bot{$token}/getMe");

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => $this->tr('tg_ok')
                ];
            }
            
            throw new \Exception('Telegram API error: ' . $response->body());
        } catch (\Exception $e) {
            $msg = $this->tr('tg_fail', ['error' => $e->getMessage()]);
            
            if ($this->isLocal()) {
                return [
                    'success' => true, // Ignore fail in local
                    'message' => $msg . $this->tr('tg_local_note')
                ];
            }
            
            return [
                'success' => false,
                'message' => $msg
            ];
        }
    }
}
