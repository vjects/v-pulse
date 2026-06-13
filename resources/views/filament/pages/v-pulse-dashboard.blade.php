<x-filament-panels::page>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Settings Panel -->
        <div class="lg:col-span-1 space-y-6">
            <x-filament::section>
                <x-slot name="heading">
                    Scope Configuration
                </x-slot>
                
                <p class="text-sm text-gray-500 mb-4">
                    Define the architecture mode. Monolith mode disables ecosystem checks.
                </p>
                
                {{ $this->form }}
                
            </x-filament::section>
        </div>

        <!-- Diagnostics Panel -->
        <div class="lg:col-span-2 space-y-6">
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
                                        wire:click="fixAction('{{ get_class($result['instance']) }}')"
                                    >
                                        {{ $result['action'] }}
                                    </x-filament::button>
                                @endif
                                
                                @if(in_array('ai', $this->data['modules'] ?? []))
                                    <x-filament::button 
                                        color="info" 
                                        icon="heroicon-o-sparkles"
                                        wire:click="analyzeWithAi('{{ get_class($result['instance']) }}')"
                                    >
                                        تحلیل با هوش مصنوعی
                                    </x-filament::button>
                                @endif
                            </div>
                        @endif
                    </div>
                </x-filament::section>
            @endforeach
        </div>
        
    </div>
</x-filament-panels::page>
