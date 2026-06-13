<?php

namespace Vjects\Pulse\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\CheckboxList;
use Filament\Actions\Action;
use Vjects\Pulse\PulseManager;

class VPulseDashboard extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cpu-chip';
    protected static ?string $navigationGroup = 'V-Pulse System';
    protected static ?string $navigationLabel = 'V-Pulse Diagnostics';
    protected static ?string $title = 'V-Pulse System Diagnostics';
    protected static ?string $slug = 'v-pulse';
    
    protected static string $view = 'v-pulse::filament.pages.v-pulse-dashboard';

    public ?array $data = [];
    public ?string $aiResponse = null;
    public ?string $aiAnalysisTitle = null;
    public ?string $lastChecked = null;

    public function mount(): void
    {
        /** @var PulseManager $manager */
        $manager = app('vjects-pulse');
        $this->form->fill($manager->getSettings());
    }
    public function form(Form $form): Form
    {
        /** @var PulseManager $manager */
        $manager = app('vjects-pulse');
        $lang = $manager->getSettings()['system_language'] ?? 'fa';
        $isFa = $lang === 'fa';
        
        return $form
            ->schema([
                \Filament\Forms\Components\Tabs::make('Settings')
                    ->tabs([
                        \Filament\Forms\Components\Tabs\Tab::make($isFa ? 'تنظیمات پایه' : 'General Scope')
                            ->schema([
                                Select::make('system_language')
                                    ->label($isFa ? 'زبان رابط کاربری سیستم' : 'System Interface Language')
                                    ->options([
                                        'fa' => 'فارسی (Persian)',
                                        'en' => 'English',
                                    ])
                                    ->default('fa')
                                    ->required()
                                    ->live(),
                                    
                                Select::make('system_environment')
                                    ->label($isFa ? 'نوع محیط اجرا' : 'Environment Type')
                                    ->options([
                                        'local' => $isFa ? 'محیط لوکال / توسعه' : 'Local / Development',
                                        'production' => $isFa ? 'محیط اصلی / پروداکشن' : 'Production / Live',
                                    ])
                                    ->default('production')
                                    ->required()
                                    ->live(),
                                    
                                Select::make('cache_interval')
                                    ->label($isFa ? 'زمان‌بندی بررسی سیستم (Cache)' : 'System Check Interval (Cache)')
                                    ->options([
                                        '0' => $isFa ? 'درلحظه (بدون کش)' : 'Real-time (No Cache)',
                                        '15' => $isFa ? 'هر ۱۵ دقیقه' : 'Every 15 Minutes',
                                        '60' => $isFa ? 'هر ۱ ساعت' : 'Every 1 Hour',
                                        '720' => $isFa ? 'هر ۱۲ ساعت' : 'Every 12 Hours',
                                    ])
                                    ->default('0')
                                    ->required(),
                                    
                                Select::make('mode')
                                    ->label($isFa ? 'معماری سیستم' : 'Architecture Mode')
                                    ->options([
                                        'monolith' => $isFa ? 'مونولیت (تک سرور)' : 'Monolith (Single Server)',
                                        'ecosystem' => $isFa ? 'اکوسیستم یکپارچه توزیع‌شده' : 'Distributed Ecosystem',
                                    ])
                                    ->required()
                                    ->live(),
                                    
                                \Filament\Forms\Components\TagsInput::make('api_ecosystem_urls')
                                    ->label($isFa ? 'آدرس سرورهای اکوسیستم' : 'API Ecosystem Base URLs')
                                    ->placeholder($isFa ? 'آدرس وارد کنید و اینتر بزنید...' : 'Enter URL and press enter...')
                                    ->helperText($isFa ? 'آدرس پایه سرورهای متصل را وارد کنید (فقط برای حالت اکوسیستم)' : 'Enter the base URLs of the connected servers (only for ecosystem mode)')
                                    ->visible(fn (\Filament\Forms\Get $get) => $get('mode') === 'ecosystem'),
                                    
                                CheckboxList::make('modules')
                                    ->label($isFa ? 'ماژول‌های فعال' : 'Active Modules')
                                    ->options([
                                        'ai' => $isFa ? 'سرویس هوش مصنوعی' : 'AI Service',
                                        'ftp' => $isFa ? 'نودهای خارجی FTP' : 'External FTP Nodes',
                                        's3' => $isFa ? 'نودهای ذخیره‌سازی S3' : 'S3 Storage Nodes',
                                        'telegram' => $isFa ? 'هشدارهای تلگرامی' : 'Telegram Alerts',
                                        'security' => $isFa ? 'اسکنر تهدیدات امنیتی' : 'Security Threat Scanner',
                                    ])
                                    ->columns(3),
                            ]),
                        
                        \Filament\Forms\Components\Tabs\Tab::make($isFa ? 'تلگرام و پروکسی' : 'Telegram & Proxy')
                            ->schema([
                                \Filament\Forms\Components\TextInput::make('telegram_bot_token')
                                    ->label($isFa ? 'توکن ربات' : 'Bot Token')
                                    ->password(),
                                    
                                \Filament\Forms\Components\TextInput::make('telegram_chat_id')
                                    ->label($isFa ? 'شناسه چت (Chat ID)' : 'Chat ID'),
                                    
                                \Filament\Forms\Components\Toggle::make('use_telegram_proxy')
                                    ->label($isFa ? 'استفاده از پروکسی' : 'Use Proxy (MTProto/HTTP)')
                                    ->live(),
                                    
                                \Filament\Forms\Components\Grid::make(3)
                                    ->schema([
                                        \Filament\Forms\Components\TextInput::make('proxy_server')
                                            ->label($isFa ? 'آدرس سرور پروکسی' : 'Proxy Server IP/Host'),
                                        \Filament\Forms\Components\TextInput::make('proxy_port')
                                            ->label($isFa ? 'پورت' : 'Proxy Port')
                                            ->numeric(),
                                        \Filament\Forms\Components\TextInput::make('proxy_secret')
                                            ->label($isFa ? 'رمز پروکسی' : 'Proxy Secret / Credentials')
                                            ->password(),
                                    ])
                                    ->visible(fn (\Filament\Forms\Get $get) => $get('use_telegram_proxy') === true)
                            ]),
                            
                        \Filament\Forms\Components\Tabs\Tab::make($isFa ? 'دستیار هوش مصنوعی' : 'AI Assistant')
                            ->schema([
                                \Filament\Forms\Components\Select::make('ai_language')
                                    ->label($isFa ? 'زبان پاسخ‌دهی هوش مصنوعی' : 'AI Response Language')
                                    ->options([
                                        'fa' => 'فارسی (Persian)',
                                        'en' => 'English',
                                    ])
                                    ->default('fa')
                                    ->required(),
                                    
                                \Filament\Forms\Components\Select::make('ai_provider')
                                    ->label($isFa ? 'سرویس‌دهنده' : 'LLM Provider')
                                    ->options([
                                        'openai' => 'OpenAI (GPT)',
                                        'google' => 'Google Gemini',
                                        'qwen' => 'Qwen',
                                    ]),
                                \Filament\Forms\Components\TextInput::make('ai_model')
                                    ->label($isFa ? 'نام مدل (Model Name)' : 'AI Model Name')
                                    ->placeholder('e.g. qwen3.6-flash, gpt-4o, gemini-1.5-pro')
                                    ->helperText($isFa ? 'مدل خاصی که دستیار باید استفاده کند را وارد کنید.' : 'Enter the specific model name you want the assistant to use.'),
                                \Filament\Forms\Components\TextInput::make('ai_api_key')
                                    ->label($isFa ? 'کلید ارتباطی (API Key)' : 'API Key')
                                    ->password(),
                            ]),
                    ])
                    ->columnSpanFull()
            ])
            ->statePath('data');
    }

    public function saveSettings(): void
    {
        /** @var PulseManager $manager */
        $manager = app('vjects-pulse');
        $manager->saveSettings($this->form->getState());
        
        \Filament\Notifications\Notification::make()
            ->title('Settings Saved')
            ->body('V-Pulse scope settings have been successfully updated.')
            ->success()
            ->send();
            
        $this->redirect(request()->header('Referer'));
    }

    /**
     * @return array<Action>
     */
    protected function getHeaderActions(): array
    {
        /** @var PulseManager $manager */
        $manager = app('vjects-pulse');
        $isFa = ($manager->getSettings()['system_language'] ?? 'fa') === 'fa';

        return [
            Action::make('testTelegram')
                ->label($isFa ? 'تست ارسال به تلگرام' : 'Test Telegram')
                ->icon('heroicon-o-paper-airplane')
                ->color('info')
                ->action('sendTelegramTest')
                ->visible(fn () => in_array('telegram', $this->data['modules'] ?? []) && !empty($this->data['telegram_bot_token'])),
                
            Action::make('forceRescan')
                ->label($isFa ? 'بررسی مجدد' : 'Rescan System')
                ->icon('heroicon-o-arrow-path')
                ->color('success')
                ->action('performRescan'),
                
            Action::make('save')
                ->label($isFa ? 'ذخیره تنظیمات' : 'Save Settings')
                ->action('saveSettings')
                ->color('primary'),
        ];
    }
    
    public function sendTelegramTest(): void
    {
        /** @var PulseManager $manager */
        $manager = app('vjects-pulse');
        $settings = $manager->getSettings();
        
        $token = $settings['telegram_bot_token'] ?? null;
        $chatId = $settings['telegram_chat_id'] ?? null;
        
        if (!$token || !$chatId) {
            \Filament\Notifications\Notification::make()
                ->title('خطا در تنظیمات')
                ->body('توکن ربات یا Chat ID تنظیم نشده است.')
                ->danger()
                ->send();
            return;
        }

        // Gather errors from checkers
        $results = $this->getCheckResults();
        $errors = array_filter($results, fn($r) => $r['status'] !== 'success');
        
        $options = [];
        if (($settings['use_telegram_proxy'] ?? false) && !empty($settings['proxy_server'])) {
            $options['proxy'] = $settings['proxy_server'] . ':' . ($settings['proxy_port'] ?? 80);
        }
        
        try {
            if (empty($errors)) {
                // Send a generic success message
                $msg = "✅ سیستم V-Pulse در سلامت کامل است.\nهیچ خطایی یافت نشد.";
                \Illuminate\Support\Facades\Http::timeout(10)->withOptions($options)
                    ->post("https://api.telegram.org/bot{$token}/sendMessage", [
                        'chat_id' => $chatId,
                        'text' => $msg,
                    ]);
            } else {
                // Send each error as a separate message
                foreach ($errors as $error) {
                    $msg = "🚨 هشدار (V-Pulse)\n\n";
                    $msg .= "نام بررسی: {$error['name']}\n";
                    $msg .= "توضیحات: {$error['message']}\n";
                    
                    \Illuminate\Support\Facades\Http::timeout(10)->withOptions($options)
                        ->post("https://api.telegram.org/bot{$token}/sendMessage", [
                            'chat_id' => $chatId,
                            'text' => $msg,
                        ]);
                }
            }
            
            \Filament\Notifications\Notification::make()
                ->title('تست با موفقیت انجام شد')
                ->body('پیام‌ها به ربات تلگرام ارسال شدند.')
                ->success()
                ->send();
                
        } catch (\Exception $e) {
            \Filament\Notifications\Notification::make()
                ->title('خطا در ارسال به تلگرام')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function performRescan(): void
    {
        \Illuminate\Support\Facades\Cache::forget('vpulse_check_results_data');
        
        /** @var PulseManager $manager */
        $manager = app('vjects-pulse');
        $isFa = ($manager->getSettings()['system_language'] ?? 'fa') === 'fa';
        
        \Filament\Notifications\Notification::make()
            ->title($isFa ? 'بررسی مجدد انجام شد' : 'Rescan Completed')
            ->success()
            ->send();
            
        $this->redirect(request()->header('Referer'));
    }

    public function getCheckResults(): array
    {
        /** @var PulseManager $manager */
        $manager = app('vjects-pulse');
        $settings = $manager->getSettings();
        $cacheMinutes = (int) ($settings['cache_interval'] ?? 0);
        
        $manager->registerChecker(\Vjects\Pulse\Checkers\DatabaseChecker::class);
        $manager->registerChecker(\Vjects\Pulse\Checkers\ApiConnectionChecker::class);
        $manager->registerChecker(\Vjects\Pulse\Checkers\TelegramConnectionChecker::class);
        $manager->registerChecker(\Vjects\Pulse\Checkers\SecurityChecker::class);
        
        if ($cacheMinutes > 0) {
            $data = \Illuminate\Support\Facades\Cache::remember('vpulse_check_results_data', now()->addMinutes($cacheMinutes), function () use ($manager) {
                return [
                    'time' => now()->toDateTimeString(),
                    'results' => $manager->runChecks(),
                ];
            });
            $this->lastChecked = $data['time'];
            return $data['results'];
        }
        
        $this->lastChecked = now()->toDateTimeString();
        return $manager->runChecks();
    }
    
    public function getLogs(): string
    {
        $logPath = storage_path('logs/vpulse.log');
        $isFa = ($this->data['system_language'] ?? 'fa') === 'fa';
        
        if (!file_exists($logPath)) {
            return $isFa ? 'هیچ خطایی ثبت نشده است.' : 'No errors logged yet.';
        }
        
        $content = file_get_contents($logPath);
        if (empty(trim($content))) {
            return $isFa ? 'هیچ خطایی ثبت نشده است.' : 'No errors logged yet.';
        }
        
        $lines = explode("\n", trim($content));
        $lines = array_slice($lines, -100);
        return implode("\n", array_reverse($lines));
    }
    
    public function clearLogs(): void
    {
        $logPath = storage_path('logs/vpulse.log');
        if (file_exists($logPath)) {
            unlink($logPath);
        }
        
        $isFa = ($this->data['system_language'] ?? 'fa') === 'fa';
        \Filament\Notifications\Notification::make()
            ->title($isFa ? 'لاگ‌ها پاک شدند' : 'Logs Cleared')
            ->success()
            ->send();
    }

    public function fixAction(string $checkerClass): void
    {
        try {
            /** @var \Vjects\Pulse\Checkers\CheckerInterface $checker */
            $checker = app($checkerClass);
            $checker->executeFix();
            
            \Filament\Notifications\Notification::make()
                ->title('Action Executed')
                ->body('The fix action was triggered successfully.')
                ->success()
                ->send();
                
            $this->redirect(request()->header('Referer'));
        } catch (\Exception $e) {
            \Filament\Notifications\Notification::make()
                ->title('Action Failed')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function analyzeWithAi(string $checkerClass): void
    {
        /** @var PulseManager $manager */
        $manager = app('vjects-pulse');
        $settings = $manager->getSettings();
        
        $provider = $settings['ai_provider'] ?? null;
        $model = $settings['ai_model'] ?? null;
        $apiKey = $settings['ai_api_key'] ?? null;
        $lang = $settings['ai_language'] ?? 'fa';
        
        if (!$apiKey) {
            \Filament\Notifications\Notification::make()
                ->title('کلید API تنظیم نشده است')
                ->body('لطفا ابتدا API Key را در تب دستیار هوش مصنوعی وارد کنید.')
                ->danger()
                ->send();
            return;
        }

        try {
            /** @var \Vjects\Pulse\Checkers\CheckerInterface $checker */
            $checker = app($checkerClass);
            $checkerName = $checker->getName();
            
            $sysLang = $lang === 'fa' ? 'Persian (فارسی)' : 'English';
            $envType = $settings['system_environment'] ?? 'production';
                
            $systemPrompt = "You are an elite DevOps Engineer. Diagnose and resolve system failures. You MUST respond entirely in {$sysLang}. Keep the response extremely short (maximum 100 words), bulleted, and highly technical. NO fluff.";
            
            $userPrompt = "Failed component: {$checkerName}\nDescription: {$checker->getDescription()}\nEnvironment: {$envType}\n\nProvide the exact steps to fix it. Response language: {$sysLang}";
            
            $url = '';
            $payload = [];
            
            if ($provider === 'qwen') {
                $url = 'https://dashscope-intl.aliyuncs.com/compatible-mode/v1/chat/completions';
                $payload = [
                    'model' => $model ?: 'qwen-turbo',
                    'messages' => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user', 'content' => $userPrompt]
                    ]
                ];
            } else if ($provider === 'openai') {
                $url = 'https://api.openai.com/v1/chat/completions';
                $payload = [
                    'model' => $model ?: 'gpt-4o',
                    'messages' => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user', 'content' => $userPrompt]
                    ]
                ];
            } else if ($provider === 'google') {
                $url = "https://generativelanguage.googleapis.com/v1beta/models/" . ($model ?: 'gemini-1.5-pro') . ":generateContent?key=" . $apiKey;
                $payload = [
                    'system_instruction' => ['parts' => [['text' => $systemPrompt]]],
                    'contents' => [
                        ['parts' => [['text' => $userPrompt]]]
                    ]
                ];
            } else {
                throw new \Exception("پروایدر هوش مصنوعی پشتیبانی نمی‌شود.");
            }
            
            $request = \Illuminate\Support\Facades\Http::timeout(45);
            
            if ($provider !== 'google') {
                $request = $request->withToken($apiKey);
            }
            
            $response = $request->post($url, $payload);
                
            if ($response->successful()) {
                if ($provider === 'google') {
                    $this->aiResponse = $response->json('candidates.0.content.parts.0.text');
                } else {
                    $this->aiResponse = $response->json('choices.0.message.content');
                }
                
                // Save to history
                $manager->saveAiAnalysis([
                    'checker' => $checkerName,
                    'date' => date('Y-m-d H:i:s'),
                    'response' => $this->aiResponse,
                ]);
                
                \Filament\Notifications\Notification::make()
                    ->title($lang === 'fa' ? 'تحلیل انجام شد' : 'Analysis Complete')
                    ->body($lang === 'fa' ? 'لطفاً برای مشاهده نتیجه به تب تاریخچه مراجعه کنید.' : 'Please go to the AI History tab to view the result.')
                    ->success()
                    ->send();
            } else {
                throw new \Exception($response->body());
            }
            
        } catch (\Exception $e) {
            \Filament\Notifications\Notification::make()
                ->title('خطا در تحلیل هوش مصنوعی')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
    
    public function viewAiHistory(string $id): void
    {
        /** @var PulseManager $manager */
        $manager = app('vjects-pulse');
        $histories = $manager->getAiHistory();
        
        foreach ($histories as $history) {
            if (($history['id'] ?? '') === $id) {
                $this->aiAnalysisTitle = "تحلیل هوشمند: {$history['checker']}";
                $this->aiResponse = $history['response'];
                
                if (!($history['is_read'] ?? false)) {
                    $manager->markAiHistoryAsRead($id);
                }
                
                $this->dispatch('open-modal', id: 'ai-analysis-modal');
                return;
            }
        }
    }
}
