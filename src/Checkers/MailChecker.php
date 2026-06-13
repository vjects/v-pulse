<?php

namespace Vjects\Pulse\Checkers;

use Illuminate\Support\Facades\Mail;

class MailChecker extends BaseChecker
{
    public function getName(): string
    {
        return $this->tr('mail_name');
    }

    public function getDescription(): string
    {
        return $this->tr('mail_desc');
    }

    public function isApplicable(array $settings): bool
    {
        return true;
    }

    public function run(): array
    {
        try {
            // Test SMTP connection via the mailer transport
            $transport = Mail::getSymfonyTransport();
            
            // Note: Since Symfony Mailer 6+, there's no native ping without sending, 
            // but we can try to start it or check local config.
            // A common way to check SMTP specifically is opening a socket to the host/port.
            $host = config('mail.mailers.smtp.host');
            $port = config('mail.mailers.smtp.port');
            $timeout = 3;

            if (empty($host)) {
                throw new \Exception('SMTP Host is not configured.');
            }

            // Using fsockopen to quickly check if port is open
            $socket = @fsockopen($host, $port, $errno, $errstr, $timeout);

            if (!$socket) {
                throw new \Exception("Could not connect to $host:$port ($errno: $errstr)");
            }

            fclose($socket);

            return [
                'success' => true,
                'message' => $this->tr('mail_ok')
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $this->tr('mail_fail', ['error' => $e->getMessage()])
            ];
        }
    }
}
