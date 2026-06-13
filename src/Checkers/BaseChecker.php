<?php

namespace Vjects\Pulse\Checkers;

abstract class BaseChecker implements CheckerInterface
{
    /**
     * Default implementation. Can be overridden.
     */
    public function isApplicable(array $settings): bool
    {
        return true;
    }

    /**
     * Default: no fix action.
     */
    public function getFixActionName(): ?string
    {
        return null;
    }

    /**
     * Default: nothing to do.
     */
    public function executeFix(): void
    {
        //
    }

    protected function getSettings(): array
    {
        $manager = app('vjects-pulse');
        return $manager->getSettings();
    }

    protected function isLocal(): bool
    {
        return ($this->getSettings()['system_environment'] ?? 'production') === 'local';
    }

    protected function getLang(): string
    {
        return $this->getSettings()['system_language'] ?? 'fa';
    }

    protected function tr(string $key, array $replace = []): string
    {
        $lang = $this->getLang();
        
        // Hardcoded translations for core checkers to avoid external dependencies
        $translations = [
            'en' => [
                'db_name' => 'Core Database Health',
                'db_desc' => 'Checks if the main database is connected and core tables exist.',
                'db_ok' => 'Database connected and migrations exist.',
                'db_fail' => 'Database connection failed: :error',
                'db_missing_seed' => 'Database connected, but missing essential baseline data: :tables',
                'db_missing_seed_action' => 'Inject Default Data (Seed)',
                'db_local_note' => ' (Local environment allows slower connections)',
                
                'api_name' => 'API Ecosystem Connection',
                'api_desc' => 'Checks if the Master server can ping the API Ecosystem server.',
                'api_ok' => 'API Ecosystem is reachable.',
                'api_fail' => 'Network request failed: :error',
                'api_no_url' => 'API Ecosystem URL is not configured in settings.',
                'api_local_note' => ' (Note: In Local environment using "php artisan serve", cURL timeouts (Error 28) are expected because the built-in server is single-threaded and cannot ping itself. This is safe to ignore.)',
                'api_fix' => 'Run Diagnose Tool',
                
                'sec_name' => 'Application Security Baseline',
                'sec_desc' => 'Checks basic security configurations like APP_DEBUG and API Rate Limiting.',
                'sec_ok' => 'Core security parameters are strictly configured.',
                'sec_fail' => 'Security Issues Detected: :issues',
                'sec_local_note' => ' (Ignored because system is in Local mode. Debug mode is allowed.)',
                'sec_fix' => 'Disable Debug Mode',
                
                'tg_name' => 'Telegram Bot Connection',
                'tg_desc' => 'Checks if the system can reach Telegram API (with or without proxy).',
                'tg_ok' => 'Telegram Bot is connected and responding.',
                'tg_fail' => 'Telegram Connection Failed: :error',
                'tg_local_note' => ' (Local environment often lacks proxy setup. Ignored.)',
                
                'queue_name' => 'Background Jobs Health',
                'queue_desc' => 'Monitors Laravel queues for failed jobs and excessive backlog.',
                'queue_ok' => 'Queue is healthy. No failed jobs and backlog is normal.',
                'queue_failed' => 'Danger: :count jobs have failed and require attention!',
                'queue_backed_up' => 'Warning: Queue is heavily backed up (:count pending jobs). Ensure Queue Worker is running!',
                'queue_fix' => 'Retry Failed Jobs',
                'queue_process' => 'Process Pending Jobs',
                
                'mail_name' => 'SMTP Mail Connection',
                'mail_desc' => 'Checks if the application can establish a socket connection with the SMTP server.',
                'mail_ok' => 'Successfully connected to the SMTP server.',
                'mail_fail' => 'SMTP Connection Failed: :error',
            ],
            'fa' => [
                'db_name' => 'سلامت دیتابیس مرکزی',
                'db_desc' => 'بررسی اتصال دیتابیس و وجود جداول اصلی (مایگریشن‌ها).',
                'db_ok' => 'دیتابیس متصل است و مایگریشن‌ها وجود دارند.',
                'db_fail' => 'خطا در اتصال به دیتابیس: :error',
                'db_missing_seed' => 'دیتابیس متصل است، اما جداول از اطلاعات پایه خالی هستند (نیاز به Seed): :tables',
                'db_missing_seed_action' => 'تزریق اطلاعات پیش‌فرض (Seed)',
                'db_local_note' => ' (در محیط لوکال تاخیر در اتصال طبیعی است)',
                
                'api_name' => 'اتصال اکوسیستم API',
                'api_desc' => 'بررسی پینگ بین سرور مستر و سرور اکوسیستم API.',
                'api_ok' => 'اکوسیستم API در دسترس است.',
                'api_fail' => 'خطای شبکه در اتصال: :error',
                'api_no_url' => 'آدرس سرور API در تنظیمات وارد نشده است.',
                'api_local_note' => ' (توجه: در محیط لوکال با php artisan serve، خطای تایم‌اوت cURL (Error 28) به دلیل سینگل‌تِرِد بودن سرور داخلی کاملاً طبیعی است و نادیده گرفته شد.)',
                'api_fix' => 'اجرای ابزار عیب‌یابی',
                
                'sec_name' => 'پایه امنیتی اپلیکیشن',
                'sec_desc' => 'بررسی تنظیمات اولیه امنیتی مانند APP_DEBUG و محدودیت ریکوئست‌ها.',
                'sec_ok' => 'پارامترهای امنیتی به درستی تنظیم شده‌اند.',
                'sec_fail' => 'مشکلات امنیتی یافت شد: :issues',
                'sec_local_note' => ' (در محیط لوکال نادیده گرفته شد. روشن بودن حالت دیباگ مجاز است.)',
                'sec_fix' => 'خاموش کردن حالت دیباگ',
                
                'tg_name' => 'اتصال ربات تلگرام',
                'tg_desc' => 'بررسی اتصال به API تلگرام (با یا بدون پروکسی).',
                'tg_ok' => 'ربات تلگرام متصل است و پاسخ می‌دهد.',
                'tg_fail' => 'خطا در اتصال تلگرام: :error',
                'tg_local_note' => ' (در محیط لوکال معمولا تنظیمات پروکسی وجود ندارد. نادیده گرفته شد.)',
                
                'queue_name' => 'سلامت صف‌بندی و پردازش‌ها',
                'queue_desc' => 'بررسی وضعیت صف‌های لاراول و شناسایی جاب‌های شکست خورده یا تلنبار شده.',
                'queue_ok' => 'صف‌ها در وضعیت سالم هستند. هیچ جاب شکست خورده‌ای وجود ندارد.',
                'queue_failed' => 'اخطار جدی: :count جاب شکست خورده یافت شد!',
                'queue_backed_up' => 'هشدار: صف بسیار شلوغ است (:count جاب در انتظار). مطمئن شوید Worker فعال است!',
                'queue_fix' => 'تلاش مجدد جاب‌های شکست خورده',
                'queue_process' => 'پردازش دستی جاب‌های صف',

                'mail_name' => 'اتصال سرور ایمیل (SMTP)',
                'mail_desc' => 'بررسی اتصال سوکت به سرور ارسال ایمیل (SMTP) برای اطمینان از سلامت سرویس ایمیل.',
                'mail_ok' => 'ارتباط با سرور ایمیل با موفقیت برقرار شد.',
                'mail_fail' => 'خطا در ارتباط با سرور ایمیل: :error',
            ]
        ];

        $text = $translations[$lang][$key] ?? $key;

        foreach ($replace as $k => $v) {
            $text = str_replace(':' . $k, $v, $text);
        }

        return $text;
    }
}
