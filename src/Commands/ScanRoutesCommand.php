<?php

namespace Vjects\Pulse\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ScanRoutesCommand extends Command
{
    protected $signature = 'v-pulse:scan-routes {--user= : The user ID to authenticate as} {--guard= : The auth guard to use}';
    protected $description = 'Scan all GET routes for HTTP 500 errors';

    public function handle()
    {
        $userId = $this->option('user');
        $guard = $this->option('guard') ?: config('auth.defaults.guard');
        
        if ($userId) {
            Auth::guard($guard)->loginUsingId($userId);
        }

        $routes = Route::getRoutes()->getRoutesByMethod()['GET'] ?? [];
        
        $results = [];
        $results[] = "=== V-Pulse 500 Error Crawler Report ===";
        $results[] = "Time: " . now()->toDateTimeString();
        $results[] = "---------------------------------------";
        
        $errorCount = 0;
        $scannedCount = 0;
        
        foreach ($routes as $route) {
            $uri = $route->uri();
            
            // Skip routes with required parameters
            if (preg_match('/\{[^\}]+\}/', $uri) && !preg_match('/\{[^\}]+\?\}/', $uri)) {
                continue;
            }
            
            // Remove optional parameter for scanning
            $testUri = preg_replace('/\{[^\}]+\?\}/', '', $uri);
            $testUri = rtrim($testUri, '/');
            if (empty($testUri)) $testUri = '/';
            
            // Skip specific routes
            if (str_starts_with($uri, '_') || str_starts_with($uri, 'api/') || str_starts_with($uri, 'sanctum/')) {
                continue;
            }
            if ($route->isFallback) {
                continue;
            }
            if (str_contains($uri, 'logout')) {
                continue;
            }
            
            try {
                // We create a fresh app instance for each route to prevent state bleeding
                $app = require base_path('bootstrap/app.php');
                $kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
                
                $request = Request::create($testUri, 'GET');
                if ($userId) {
                    $app->make('auth')->guard($guard)->loginUsingId($userId);
                }
                
                $response = $kernel->handle($request);
                $status = $response->getStatusCode();
                
                if ($status >= 500) {
                    $errorCount++;
                    $results[] = "[FAIL] /{$testUri} - Status: {$status}";
                    
                    // If it's an exception, try to get it
                    if (isset($response->exception) && $response->exception) {
                        $e = $response->exception;
                        $results[] = "       Exception: " . get_class($e);
                        $results[] = "       Message: " . $e->getMessage();
                        $results[] = "       File: " . $e->getFile() . ":" . $e->getLine();
                    }
                } else {
                    $results[] = "[OK] /{$testUri} - Status: {$status}";
                }
                
                $kernel->terminate($request, $response);
                $scannedCount++;
                
            } catch (\Throwable $e) {
                $errorCount++;
                $results[] = "[FATAL] /{$testUri} - Exception: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine();
            }
        }
        
        $results[] = "---------------------------------------";
        $results[] = "Total Routes Scanned: {$scannedCount}";
        $results[] = "Total Errors Found: {$errorCount}";
        
        $this->line(implode(PHP_EOL, $results));
        
        return $errorCount > 0 ? 1 : 0;
    }
}
