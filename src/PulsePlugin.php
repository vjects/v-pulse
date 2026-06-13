<?php

namespace Vjects\Pulse;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Vjects\Pulse\Filament\Pages\VPulseDashboard;

class PulsePlugin implements Plugin
{
    public function getId(): string
    {
        return 'vjects-pulse';
    }

    public function register(Panel $panel): void
    {
        $panel->pages([
            VPulseDashboard::class,
        ]);
    }

    public function boot(Panel $panel): void
    {
        //
    }

    public static function make(): static
    {
        return app(static::class);
    }
}
