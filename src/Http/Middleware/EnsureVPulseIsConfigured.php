<?php

namespace Vjects\Pulse\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Vjects\Pulse\Filament\Pages\VPulseDashboard;

class EnsureVPulseIsConfigured
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $pulseManager = app('vjects-pulse');

        // Check if user is logged into Filament admin
        // and V-Pulse is not yet configured
        if (!$pulseManager->isConfigured()) {
            
            // Allow access if they are already on the V-Pulse page or Livewire update routes
            if ($request->routeIs('filament.admin.pages.v-pulse-dashboard') || $request->routeIs('livewire.update')) {
                return $next($request);
            }

            // Redirect to V-Pulse Onboarding
            return redirect(VPulseDashboard::getUrl());
        }

        return $next($request);
    }
}
