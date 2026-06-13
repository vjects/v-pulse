<?php

namespace Vjects\Pulse\Checkers;

use Illuminate\Support\Facades\Http;

class TelegramConnectionChecker extends BaseChecker
{
    public function getName(): string
    {
        return 'Telegram API Connectivity';
    }

    public function getDescription(): string
    {
        return 'Checks if the server can reach Telegram API directly or via MTProto/HTTP proxy.';
    }

    public function isApplicable(array $settings): bool
    {
        // Only run if telegram module is active
        return in_array('telegram', $settings['modules'] ?? []);
    }

    public function run(): array
    {
        /** @var \Vjects\Pulse\PulseManager $manager */
        $manager = app('vjects-pulse');
        $settings = $manager->getSettings();
        
        $token = $settings['telegram_bot_token'] ?? null;
        
        if (empty($token)) {
            return [
                'success' => false,
                'message' => 'Telegram Bot Token is not configured in V-Pulse settings.',
            ];
        }

        try {
            $http = Http::timeout(5);
            
            // Check if proxy is enabled
            if (isset($settings['use_telegram_proxy']) && $settings['use_telegram_proxy']) {
                $proxyServer = $settings['proxy_server'] ?? '';
                $proxyPort = $settings['proxy_port'] ?? '';
                
                if (empty($proxyServer) || empty($proxyPort)) {
                    return [
                        'success' => false,
                        'message' => 'Proxy is enabled but server/port are missing.',
                    ];
                }
                
                // Set proxy for Guzzle
                // For MTProto or SOCKS5, the format is slightly different, but here is a standard proxy injection
                $proxyUrl = "tcp://{$proxyServer}:{$proxyPort}";
                $http->withOptions(['proxy' => $proxyUrl]);
            }

            $response = $http->get("https://api.telegram.org/bot{$token}/getMe");

            if ($response->successful()) {
                $botName = $response->json('result.first_name') ?? 'Unknown Bot';
                return [
                    'success' => true,
                    'message' => "Successfully connected to Telegram. Bot: {$botName}",
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to connect to Telegram: HTTP ' . $response->status(),
            ];

        } catch (\Exception $e) {
            $msg = 'Telegram API is unreachable.';
            if (!isset($settings['use_telegram_proxy']) || !$settings['use_telegram_proxy']) {
                $msg .= ' The server might be filtered (Iran). Try enabling Proxy in settings.';
            } else {
                $msg .= ' The proxy connection failed.';
            }
            
            return [
                'success' => false,
                'message' => $msg . ' Error: ' . $e->getMessage(),
            ];
        }
    }

    public function getFixActionName(): ?string
    {
        return null;
    }
}
