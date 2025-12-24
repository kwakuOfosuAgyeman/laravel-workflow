<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\RateLimiter;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureRateLimiting();
    }

    protected function configureRateLimiting(): void
    {
        // Default API rate limiter
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // Workflow execution rate limiter - stricter limits
        RateLimiter::for('workflow_runs', function (Request $request) {
            return [
                // 10 runs per minute per IP
                Limit::perMinute(10)->by($request->ip())->response(function () {
                    return response()->json([
                        'message' => 'Too many workflow runs. Please wait before trying again.'
                    ], 429);
                }),

                // 100 runs per hour per IP
                Limit::perHour(100)->by($request->ip())->response(function () {
                    return response()->json([
                        'message' => 'Hourly workflow run limit exceeded. Please try again later.'
                    ], 429);
                }),
            ];
        });

        // Step creation/modification - moderate limits
        RateLimiter::for('step_operations', function (Request $request) {
            return Limit::perMinute(30)->by($request->ip());
        });
    }
}
