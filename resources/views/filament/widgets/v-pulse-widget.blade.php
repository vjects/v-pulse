<x-filament-widgets::widget>
    <style>
        /* Base styles that work anywhere regardless of Tailwind setup */
        .diag-widget-container {
            font-family: inherit;
        }
        .diag-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 1.25rem;
            margin-top: 1.5rem;
        }
        .diag-card {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            border-radius: 1rem;
            border: 1px solid rgba(156, 163, 175, 0.2);
            background-color: rgba(31, 41, 55, 0.03);
            padding: 1.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            transition: all 0.2s ease;
        }
        .dark .diag-card {
            background-color: rgba(17, 24, 39, 0.5); /* dark mode bg */
            border-color: rgba(255, 255, 255, 0.1);
        }
        .diag-card:hover {
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }
        .diag-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
        }
        .diag-icon-title {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .diag-title {
            font-size: 1.05rem;
            font-weight: 600;
            margin: 0;
            opacity: 0.9;
            color: inherit;
        }
        .diag-desc {
            margin: 1.25rem 0;
            font-size: 0.9rem;
            opacity: 0.6;
            line-height: 1.6;
        }
        .diag-badge {
            border-radius: 0.5rem;
            padding: 0.35rem 0.75rem;
            font-size: 0.75rem;
            font-weight: 700;
            white-space: nowrap;
        }
        .diag-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            width: 100%;
            border-radius: 0.5rem;
            background-color: #3b82f6;
            padding: 0.75rem;
            font-size: 0.875rem;
            font-weight: 600;
            color: white;
            border: none;
            cursor: pointer;
            transition: background-color 0.2s;
            text-decoration: none;
        }
        .diag-btn:hover {
            background-color: #2563eb;
        }
        
        /* Status Colors using safe hex values with opacity */
        .status-success .diag-icon-wrap { background-color: #22c55e20; }
        .status-success .diag-icon { color: #22c55e; }
        .status-success .diag-badge { background-color: #22c55e20; color: #22c55e; border: 1px solid #22c55e40; }
        
        .status-danger .diag-icon-wrap { background-color: #ef444420; }
        .status-danger .diag-icon { color: #ef4444; }
        .status-danger .diag-badge { background-color: #ef444420; color: #ef4444; border: 1px solid #ef444440; }

        .diag-icon-wrap {
            display: flex;
            border-radius: 9999px;
            padding: 0.6rem;
        }
        .diag-icon {
            width: 1.75rem;
            height: 1.75rem;
        }
    </style>

    <x-filament::section>
        <div class="diag-widget-container">
            <!-- Header -->
            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <div style="border-radius: 0.75rem; background-color: #3b82f620; padding: 0.75rem;">
                        <x-heroicon-o-cpu-chip style="width: 2rem; height: 2rem; color: #3b82f6;" />
                    </div>
                    <div>
                        <h2 style="font-size: 1.25rem; font-weight: bold; margin: 0; color: inherit;">سیستم عیب‌یابی خودکار زیرساخت (V-Pulse)</h2>
                        <p style="font-size: 0.875rem; opacity: 0.6; margin: 0.25rem 0 0 0;">وضعیت زنده اتصال سرویس‌ها و ماژول‌ها</p>
                    </div>
                </div>
                
                <button wire:click="loadDiagnostics" type="button" style="display: inline-flex; align-items: center; gap: 0.5rem; border-radius: 0.5rem; border: 1px solid rgba(156, 163, 175, 0.4); background: transparent; padding: 0.5rem 1rem; color: inherit; cursor: pointer; font-weight: 500;">
                    <x-heroicon-m-arrow-path style="width: 1.25rem; height: 1.25rem;" />
                    <span>اسکن مجدد زیرساخت</span>
                </button>
            </div>

            <div>
                @if($isLoading)
                    <div style="display: flex; align-items: center; justify-content: center; gap: 0.75rem; margin-top: 3rem; margin-bottom: 2rem; opacity: 0.7;">
                        <x-filament::loading-indicator class="h-6 w-6" />
                        <span style="font-weight: 500;">در حال ارتباط با سرورها و پردازش وضعیت...</span>
                    </div>
                @elseif(!$hasScanned)
                    <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 1rem; margin-top: 3rem; margin-bottom: 2rem; text-align: center; opacity: 0.8;">
                        <x-heroicon-o-shield-check style="width: 4rem; height: 4rem; color: #9ca3af;" />
                        <h3 style="font-size: 1.1rem; font-weight: 600; margin: 0;">آماده برای بررسی وضعیت سیستم</h3>
                        <p style="font-size: 0.9rem; max-width: 400px; margin: 0;">برای جلوگیری از فشار به سرور، اسکن به صورت خودکار انجام نمی‌شود. لطفاً برای مشاهده سلامت سیستم روی دکمه اسکن کلیک کنید.</p>
                        <button wire:click="loadDiagnostics" type="button" style="margin-top: 0.5rem; display: inline-flex; align-items: center; gap: 0.5rem; border-radius: 0.5rem; background-color: #3b82f6; padding: 0.75rem 1.5rem; color: white; cursor: pointer; font-weight: 600; border: none;">
                            <x-heroicon-m-play style="width: 1.25rem; height: 1.25rem;" />
                            <span>شروع اسکن زیرساخت</span>
                        </button>
                    </div>
                @else
                    <!-- Grid -->
                    <div class="diag-grid" dir="ltr">
                        @foreach ($results as $result)
                            <div class="diag-card status-{{ $result['status'] }}">
                                <div class="diag-top">
                                    <div class="diag-icon-title">
                                        <div class="diag-icon-wrap">
                                            @if ($result['status'] === 'success')
                                                <x-heroicon-o-check-circle class="diag-icon" />
                                            @else
                                                <x-heroicon-o-x-circle class="diag-icon" />
                                            @endif
                                        </div>
                                        <h3 class="diag-title">{{ $result['name'] }}</h3>
                                    </div>
                                    
                                    <div class="diag-badge">
                                        @if ($result['status'] === 'success') Operational
                                        @else Failing
                                        @endif
                                    </div>
                                </div>

                                <p class="diag-desc" dir="rtl" style="text-align: right;">{{ $result['message'] }}</p>
                                
                                <div style="min-height: 2.75rem;">
                                    @if ($result['status'] !== 'success')
                                        <a href="/admin/v-pulse" class="diag-btn">
                                            <x-heroicon-m-wrench-screwdriver style="width: 1.25rem; height: 1.25rem;" />
                                            <span>Auto-Fix در پنل کنترل</span>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
