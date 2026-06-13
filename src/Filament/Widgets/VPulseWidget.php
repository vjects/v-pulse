<?php

namespace Vjects\Pulse\Filament\Widgets;

use Filament\Widgets\Widget;
use Vjects\Pulse\PulseManager;

class VPulseWidget extends Widget
{
    protected static string $view = 'v-pulse::filament.widgets.v-pulse-widget';
    protected int | string | array $columnSpan = 'full';
    protected static ?int $sort = -1;

    public array $results = [];
    public bool $isLoading = true;

    public function loadDiagnostics()
    {
        $manager = app('vjects-pulse');
        
        // Register default checkers
        $manager->registerChecker(\Vjects\Pulse\Checkers\DatabaseChecker::class);
        $manager->registerChecker(\Vjects\Pulse\Checkers\ApiConnectionChecker::class);
        $manager->registerChecker(\Vjects\Pulse\Checkers\TelegramConnectionChecker::class);
        $manager->registerChecker(\Vjects\Pulse\Checkers\SecurityChecker::class);
        
        $rawResults = $manager->runChecks();
        
        // Strip out non-serializable objects to prevent Livewire hydration errors
        foreach ($rawResults as &$res) {
            unset($res['instance']);
        }
        
        $this->results = $rawResults;
        $this->isLoading = false;
    }
}
