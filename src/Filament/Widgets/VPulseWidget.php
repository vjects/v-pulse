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
        $this->results = $manager->runAllCheckers();
        $this->isLoading = false;
    }
}
