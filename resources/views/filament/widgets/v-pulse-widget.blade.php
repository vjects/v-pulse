<x-filament-widgets::widget>
    <x-filament::section>
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-bold flex items-center gap-2">
                <x-heroicon-o-cpu-chip class="w-6 h-6 text-primary-500" />
                وضعیت سلامت سیستم (V-Pulse)
            </h2>
            <x-filament::button wire:click="loadDiagnostics" size="sm" color="gray">
                اجرای مجدد
            </x-filament::button>
        </div>

        <div wire:init="loadDiagnostics">
            @if($isLoading)
                <div class="flex items-center gap-2 text-gray-500 animate-pulse">
                    <x-filament::loading-indicator class="h-5 w-5" />
                    <span class="font-mono text-sm">در حال بررسی زیرساخت...</span>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    @foreach($results as $key => $result)
                        <div class="p-4 rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 flex flex-col gap-2">
                            <div class="flex justify-between items-center">
                                <span class="font-medium text-sm text-gray-700 dark:text-gray-300">
                                    {{ \Illuminate\Support\Str::headline($key) }}
                                </span>
                                @if(isset($result['status']) && $result['status'] === 'ok')
                                    <span class="w-3 h-3 rounded-full bg-success-500 animate-pulse shadow-[0_0_10px_rgba(34,197,94,0.5)]"></span>
                                @else
                                    <span class="w-3 h-3 rounded-full bg-danger-500 animate-pulse shadow-[0_0_10px_rgba(239,68,68,0.5)]"></span>
                                @endif
                            </div>
                            <span class="text-xs text-gray-500 truncate" title="{{ $result['message'] ?? 'N/A' }}">
                                {{ $result['message'] ?? 'بررسی انجام شد' }}
                            </span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
