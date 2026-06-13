<x-filament-panels::page>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        @php
            $isFa = ($this->data['system_language'] ?? 'fa') === 'fa';
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
        <div class="lg:col-span-2 space-y-6" x-data="{ activeTab: 'checks' }">
            <x-filament::tabs>
                <x-filament::tabs.item 
                    alpine-active="activeTab === 'checks'" 
                    x-on:click="activeTab = 'checks'"
                >
                    {{ $isFa ? 'وضعیت سیستم' : 'System Status' }}
                </x-filament::tabs.item>
                
                <x-filament::tabs.item 
                    alpine-active="activeTab === 'logs'" 
                    x-on:click="activeTab = 'logs'"
                >
                    {{ $isFa ? 'لاگ سیستم' : 'System Logs' }}
                </x-filament::tabs.item>
            </x-filament::tabs>

            <div class="mt-6">
                <!-- Checks Tab -->
                <div x-show="activeTab === 'checks'" class="space-y-6">
                    @php
                        $results = $this->getCheckResults();
                    @endphp
                    
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
                                                wire:click="fixAction('{{ addslashes(get_class($result['instance'])) }}')"
                                            >
                                                {{ $result['action'] }}
                                            </x-filament::button>
                                        @endif
                                        
                                        @if(in_array('ai', $this->data['modules'] ?? []))
                                            <x-filament::button 
                                                color="info" 
                                                icon="heroicon-o-sparkles"
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
                        <pre class="bg-gray-900 text-gray-100 p-4 rounded-lg overflow-x-auto text-sm" dir="ltr" style="max-height: 400px; overflow-y: auto;">{{ $this->getLogs() }}</pre>
                    </x-filament::section>
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
