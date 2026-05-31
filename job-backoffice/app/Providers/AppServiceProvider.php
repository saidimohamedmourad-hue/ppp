<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

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
        $this->configureAuthRateLimiters();
    }

    /**
     * Brute-force / credential-stuffing protections.
     *
     * Each limiter returns multiple Limit instances so the *strictest* one
     * triggers first — e.g. login is throttled per-IP **and** per-email so an
     * attacker can't bypass the IP cap by rotating proxies against a single
     * account, nor target many accounts from one IP.
     */
    private function configureAuthRateLimiters(): void
    {
        // /api/login — 5 attempts per minute per IP, 5 per minute per email.
        RateLimiter::for('login', function (Request $request) {
            $email = (string) $request->input('email');

            return [
                Limit::perMinute(5)->by('login-ip:'.$request->ip()),
                Limit::perMinute(5)->by('login-email:'.strtolower($email)),
            ];
        });

        // /api/forgot-password — tighter to prevent both spam and enumeration.
        // 3 per email per minute on top of the existing 10/min/IP throttle.
        RateLimiter::for('forgot-password', function (Request $request) {
            $email = (string) $request->input('email');

            return [
                Limit::perMinute(10)->by('forgot-ip:'.$request->ip()),
                Limit::perMinute(3)->by('forgot-email:'.strtolower($email)),
            ];
        });
    }
}
