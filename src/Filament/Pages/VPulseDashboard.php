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
    protected static ?string $navigationLabel = 'V-Pulse Diagnostics';
    protected static ?string $title = 'V-Pulse System Diagnostics';
    protected static ?string $slug = 'v-pulse';
    
    protected static string $view = 'v-pulse::filament.pages.v-pulse-dashboard';

    public ?array $data = [];

    public function mount(): void
    {
        /** @var PulseManager $manager */
        $manager = app('vjects-pulse');
        $this->form->fill($manager->getSettings());
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                \Filament\Forms\Components\Tabs::make('Settings')
                    ->tabs([
                        \Filament\Forms\Components\Tabs\Tab::make('General Scope')
                            ->schema([
                                Select::make('mode')
                                    ->label('Architecture Mode')
                                    ->options([
                                        'monolith' => 'Monolith (Single Server)',
                                        'ecosystem' => 'Distributed Ecosystem',
                                    ])
                                    ->required()
                                    ->live(),
                                    
                                CheckboxList::make('modules')
                                    ->label('Active Modules')
                                    ->options([
                                        'ai' => 'AI Service',
                                        'ftp' => 'External FTP Nodes',
                                        's3' => 'S3 Storage Nodes',
                                        'telegram' => 'Telegram Alerts',
                                        'security' => 'Security Threat Scanner',
                                    ])
                                    ->columns(3),
                            ]),
                        
                        \Filament\Forms\Components\Tabs\Tab::make('Telegram & Proxy')
                            ->schema([
                                \Filament\Forms\Components\TextInput::make('telegram_bot_token')
                                    ->label('Bot Token')
                                    ->password(),
                                    
                                \Filament\Forms\Components\TextInput::make('telegram_chat_id')
                                    ->label('Chat ID'),
                                    
                                \Filament\Forms\Components\Toggle::make('use_telegram_proxy')
                                    ->label('Use Proxy (MTProto/HTTP)')
                                    ->live(),
                                    
                                \Filament\Forms\Components\Grid::make(3)
                                    ->schema([
                                        \Filament\Forms\Components\TextInput::make('proxy_server')
                                            ->label('Proxy Server IP/Host'),
                                        \Filament\Forms\Components\TextInput::make('proxy_port')
                                            ->label('Proxy Port')
                                            ->numeric(),
                                        \Filament\Forms\Components\TextInput::make('proxy_secret')
                                            ->label('Proxy Secret / Credentials')
                                            ->password(),
                                    ])
                                    ->visible(fn (\Filament\Forms\Get $get) => $get('use_telegram_proxy') === true)
                            ]),
                            
                        \Filament\Forms\Components\Tabs\Tab::make('AI Assistant')
                            ->schema([
                                \Filament\Forms\Components\Select::make('ai_provider')
                                    ->label('LLM Provider')
                                    ->options([
                                        'openai' => 'OpenAI (GPT)',
                                        'google' => 'Google Gemini',
                                        'qwen' => 'Qwen',
                                    ]),
                                \Filament\Forms\Components\TextInput::make('ai_api_key')
                                    ->label('API Key')
                                    ->password(),
                            ]),
                    ])
                    ->columnSpanFull(),
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
        return [
            Action::make('save')
                ->label('Save Settings')
                ->action('saveSettings')
                ->color('primary'),
        ];
    }

    public function getCheckResults(): array
    {
        /** @var PulseManager $manager */
        $manager = app('vjects-pulse');
        
        $manager->registerChecker(\Vjects\Pulse\Checkers\DatabaseChecker::class);
        $manager->registerChecker(\Vjects\Pulse\Checkers\ApiConnectionChecker::class);
        $manager->registerChecker(\Vjects\Pulse\Checkers\TelegramConnectionChecker::class);
        $manager->registerChecker(\Vjects\Pulse\Checkers\SecurityChecker::class);
        
        return $manager->runChecks();
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
        // Stub for LLM integration.
        // In a full implementation, this will send the checker's failing status 
        // to the selected LLM provider (Qwen, GPT, etc.) and return a suggestion.
        
        \Filament\Notifications\Notification::make()
            ->title('AI Analysis Initiated')
            ->body('Sending diagnostic data to the LLM agent...')
            ->info()
            ->send();
            
        // ... AI Agent API call logic goes here ...
    }
}
