<x-filament-panels::page>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        @php
            $isFa = ($this->data['system_language'] ?? 'fa') === 'fa';
            $pulseManager = app('vjects-pulse');
            $histories = $pulseManager->getAiHistory();
        @endphp
        <!-- Settings Panel -->
        <div class="lg:col-span-1 space-y-6">
            <x-filament::section>
                <x-slot name="heading">
                    {{ $isFa ? 'پیکربندی ساختاری V-Pulse' : 'Scope Configuration' }}
                </x-slot>
                
                <p class="text-sm text-gray-500 mb-4">
                    {{ $isFa ? 'تنظیمات پایه‌ای ماژول‌ها و معماری سرورها را اینجا مشخص کنید.' : 'Define the architecture mode and module settings here.' }}
                </p>
                
                {{ $this->form }}
                
            </x-filament::section>
        </div>

        <!-- Diagnostics Panel -->
        <div class="lg:col-span-2 space-y-6" x-data="{ activeTab: '{{ $this->isConfigured() ? 'checks' : 'settings' }}' }">
            <x-filament::tabs class="mb-6">
                <x-filament::tabs.item
                    x-on:click="activeTab = 'settings'"
                    x-bind:class="{ 'bg-white text-primary-600 shadow-sm dark:bg-gray-800 dark:text-primary-400': activeTab === 'settings' }"
                    class="font-medium px-4 py-2"
                >
                    {{ $isFa ? 'تنظیمات (Scope)' : 'Scope Settings' }}
                </x-filament::tabs.item>
                
                @if($this->isConfigured())
                    <x-filament::tabs.item
                        x-on:click="activeTab = 'checks'"
                        x-bind:class="{ 'bg-white text-primary-600 shadow-sm dark:bg-gray-800 dark:text-primary-400': activeTab === 'checks' }"
                        class="font-medium px-4 py-2"
                    >
                        {{ $isFa ? 'وضعیت سیستم' : 'System Status' }}
                    </x-filament::tabs.item>

                    <x-filament::tabs.item 
                        alpine-active="activeTab === 'logs'" 
                        x-on:click="activeTab = 'logs'; $wire.loadLogs()"
                    >
                        {{ $isFa ? 'لاگ سیستم' : 'System Logs' }}
                    </x-filament::tabs.item>
                    
                    <x-filament::tabs.item 
                        alpine-active="activeTab === 'ai_history'" 
                        x-on:click="activeTab = 'ai_history'"
                    >
                        <div class="flex items-center gap-2 relative">
                            {{ $isFa ? 'تاریخچه هوش مصنوعی' : 'AI History' }}
                            
                            @php
                                $hasUnread = collect($histories)->where('is_read', false)->count() > 0;
                            @endphp
                            
                            @if($hasUnread)
                                <span class="flex h-3 w-3 absolute -top-1 -right-4" wire:poll.60s>
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-info-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-3 w-3 bg-info-500"></span>
                                </span>
                            @endif
                        </div>
                    </x-filament::tabs.item>
                @endif
            </x-filament::tabs>

            <div class="mt-6">
                <!-- Settings Tab -->
                <div x-show="activeTab === 'settings'">
                    
                    @if(!$this->isConfigured())
                        <div class="mb-6 p-4 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-lg relative overflow-hidden">
                            <div class="absolute right-0 top-0 opacity-10">
                                <x-filament::icon icon="heroicon-o-sparkles" class="w-32 h-32 transform translate-x-8 -translate-y-8" />
                            </div>
                            <div class="relative z-10">
                                <h2 class="text-xl font-bold mb-2 flex items-center gap-2">
                                    <x-filament::icon icon="heroicon-o-rocket-launch" class="w-6 h-6" />
                                    {{ $isFa ? 'به سیستم تشخیص هوشمند V-Pulse خوش آمدید!' : 'Welcome to V-Pulse Smart Diagnostics!' }}
                                </h2>
                                <p class="opacity-90 max-w-2xl text-sm leading-relaxed">
                                    {{ $isFa ? 'برای راه‌اندازی و فعال‌سازی این سیستم، ابتدا باید تنظیمات پایه (مثل زبان سیستم، معماری محیط اجرا و ماژول‌ها) را از فرم زیر پیکربندی کرده و دکمه "ذخیره تنظیمات" در بالا را کلیک کنید تا سایر بخش‌ها فعال شوند.' : 'To initialize the system, please configure the basic settings below (e.g., system language, architecture mode) and click "Save Settings" at the top to unlock the diagnostic modules.' }}
                                </p>
                            </div>
                        </div>
                    @endif
                    <form wire:submit="saveSettings">
                        {{ $this->form }}
                    </form>
                </div>

                <!-- Checks Tab -->
                <div x-show="activeTab === 'checks'" class="space-y-6">
                    @php
                        $results = $this->getCheckResults();
                    @endphp
                    
                    @if($this->lastChecked)
                        <div class="text-xs text-gray-500 font-mono flex items-center justify-end gap-1 opacity-75" dir="ltr">
                            <x-filament::icon icon="heroicon-o-clock" class="h-3 w-3" />
                            Last checked: {{ $this->lastChecked }}
                        </div>
                    @endif

                    <x-filament::section>
                        <h3 class="font-bold mb-4 flex items-center gap-2">
                            <x-filament::icon icon="heroicon-o-squares-2x2" class="h-5 w-5 text-primary-500" />
                            {{ $isFa ? 'خلاصه وضعیت سیستم' : 'System Overview' }}
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                            @foreach($results as $result)
                                <div class="flex items-center gap-3 p-3 rounded-lg border dark:border-gray-700 bg-gray-50 dark:bg-gray-800 shadow-sm transition-all hover:shadow-md">
                                    @if($result['status'] === 'success')
                                        <span class="relative flex h-3 w-3">
                                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-success-400 opacity-75"></span>
                                            <span class="relative inline-flex rounded-full h-3 w-3 bg-success-500"></span>
                                        </span>
                                    @else
                                        <span class="relative flex h-3 w-3">
                                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-danger-400 opacity-75"></span>
                                            <span class="relative inline-flex rounded-full h-3 w-3 bg-danger-500"></span>
                                        </span>
                                    @endif
                                    <span class="text-sm font-medium truncate">{{ $result['name'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </x-filament::section>
                    
                    @foreach($results as $result)
                        <x-filament::section>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-4">
                                    @if($result['status'] === 'success')
                                        <x-filament::icon
                                            icon="heroicon-o-check-circle"
                                            class="h-8 w-8 text-success-500"
                                        />
                                    @else
                                        <x-filament::icon
                                            icon="heroicon-o-exclamation-triangle"
                                            class="h-8 w-8 text-danger-500"
                                        />
                                    @endif
                                    
                                    <div>
                                        <h3 class="font-bold text-lg">{{ $result['name'] }}</h3>
                                        <p class="text-sm text-gray-500">{{ $result['description'] }}</p>
                                        <p class="text-sm font-mono mt-2 @if($result['status'] === 'success') text-success-600 @else text-danger-600 @endif">
                                            {{ $result['message'] }}
                                        </p>
                                    </div>
                                </div>
                                
                                @if($result['status'] !== 'success')
                                    <div class="flex gap-2">
                                        @if($result['action'])
                                            <x-filament::button 
                                                color="warning" 
                                                wire:click="mountAction('performFixAction', { checker: '{{ addslashes(get_class($result['instance'])) }}' })"
                                            >
                                                {{ $result['action'] }}
                                            </x-filament::button>
                                        @endif
                                        
                                        @if(in_array('ai', $this->data['modules'] ?? []))
                                            <x-filament::button 
                                                color="info" 
                                                icon="heroicon-o-sparkles"
                                                x-on:click="activeTab = 'ai_history'"
                                                wire:click="analyzeWithAi('{{ addslashes(get_class($result['instance'])) }}')"
                                            >
                                                {{ $isFa ? 'تحلیل با هوش مصنوعی' : 'AI Analysis' }}
                                            </x-filament::button>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </x-filament::section>
                    @endforeach
                </div>
                
                <!-- Logs Tab -->
                <div x-show="activeTab === 'logs'" x-cloak>
                    <x-filament::section>
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="font-bold">{{ $isFa ? 'گزارش خطاهای V-Pulse' : 'V-Pulse Error Logs' }}</h3>
                            <x-filament::button color="danger" size="sm" wire:click="clearLogs">
                                {{ $isFa ? 'پاک کردن لاگ‌ها' : 'Clear Logs' }}
                            </x-filament::button>
                        </div>
                        <pre class="bg-gray-900 text-gray-100 p-4 rounded-lg overflow-x-auto text-sm" dir="ltr" style="max-height: 400px; overflow-y: auto;">{{ $loadedLogs ?? 'Loading logs...' }}</pre>
                    </x-filament::section>
                </div>
                
                <!-- AI History Tab -->
                <div x-show="activeTab === 'ai_history'" x-cloak class="space-y-4">
                    <div wire:loading wire:target="analyzeWithAi" class="w-full">
                        <x-filament::section>
                            <div class="flex items-center gap-3 text-info-500">
                                <x-filament::loading-indicator class="h-6 w-6" />
                                <span class="font-bold">{{ $isFa ? 'در حال برقراری ارتباط با هوش مصنوعی و تحلیل سیستم (لطفاً منتظر بمانید)...' : 'Communicating with AI and analyzing system (Please wait)...' }}</span>
                            </div>
                        </x-filament::section>
                    </div>
                    
                    @forelse($histories as $history)
                        <x-filament::section class="{{ !($history['is_read'] ?? false) ? 'ring-2 ring-info-500' : '' }}">
                            <div class="flex justify-between items-center">
                                <div class="flex items-center gap-3">
                                    <div class="p-2 rounded-lg {{ !($history['is_read'] ?? false) ? 'bg-info-500/10 text-info-500' : 'bg-gray-100 dark:bg-gray-800 text-gray-500' }}">
                                        <x-filament::icon icon="heroicon-o-sparkles" class="h-6 w-6" />
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-lg flex items-center gap-2">
                                            {{ $history['checker'] ?? 'Unknown' }}
                                            @if(!($history['is_read'] ?? false))
                                                <span class="inline-flex items-center rounded-md bg-info-50 px-2 py-1 text-xs font-medium text-info-700 ring-1 ring-inset ring-info-600/20 dark:bg-info-500/10 dark:text-info-400 dark:ring-info-500/20">جدید</span>
                                            @endif
                                        </h3>
                                        <span class="text-sm text-gray-500 font-mono" dir="ltr">{{ $history['date'] ?? '' }}</span>
                                    </div>
                                </div>
                                
                                <x-filament::button 
                                    color="gray" 
                                    wire:click="viewAiHistory('{{ $history['id'] ?? '' }}')"
                                >
                                    {{ $isFa ? 'نمایش پاسخ' : 'View Response' }}
                                </x-filament::button>
                            </div>
                        </x-filament::section>
                    @empty
                        <x-filament::section>
                            <p class="text-gray-500 text-center py-6">
                                {{ $isFa ? 'هیچ تحلیلی هنوز ثبت نشده است.' : 'No AI analyses recorded yet.' }}
                            </p>
                        </x-filament::section>
                    @endforelse
                </div>
            </div>
        </div>
        
    </div>

    <x-filament::modal id="ai-analysis-modal" width="3xl">
        <x-slot name="heading">
            {{ $aiAnalysisTitle ?? 'تحلیل هوش مصنوعی' }}
        </x-slot>

        <div class="prose dark:prose-invert max-w-none text-sm" style="line-height: 1.8;" dir="rtl">
            {!! \Illuminate\Support\Str::markdown($aiResponse ?? 'در حال پردازش...') !!}
        </div>
        
        <x-slot name="footer">
            <x-filament::button color="gray" wire:click="$dispatch('close-modal', { id: 'ai-analysis-modal' })">
                بستن
            </x-filament::button>
        </x-slot>
    </x-filament::modal>

</x-filament-panels::page>
