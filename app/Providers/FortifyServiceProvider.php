<?php

namespace App\Providers;

use App\Actions\Fortify\ResetUserPassword;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
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
     *
     * Per RESEARCH §Pattern 2 + Pitfall 8:
     * - Binds the 3 Blade views (login, forgot-password, reset-password)
     * - Defines the login rate limiter (5 attempts/min by lowercased email + IP)
     * - Only resetPasswords action is wired — no CreateNewUser (D-09: Pierre seeded)
     */
    public function boot(): void
    {
        // Headless Fortify — bind Blade views manually (D-03)
        Fortify::loginView(fn () => view('auth.login'));
        Fortify::requestPasswordResetLinkView(fn () => view('auth.forgot-password'));
        Fortify::resetPasswordView(fn ($request) => view('auth.reset-password', ['request' => $request]));

        // Wire password reset action (only Fortify feature enabled in Phase 1)
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        // Login rate limiter: 5 attempts/minute by lowercased email + IP
        // (RESEARCH §Pattern 2 + Pitfall 8, UI-SPEC §Auth error 'Trop de tentatives')
        RateLimiter::for('login', function (Request $request) {
            $throttleKey = mb_strtolower((string) $request->input('email')).'|'.$request->ip();

            return Limit::perMinute(5)->by($throttleKey);
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });

        // Magic link rate limiter (D-52) :
        // - 5 tentatives/heure par IP
        // - 3 tentatives/24h par adresse email
        RateLimiter::for('magic-link', function (Request $request) {
            return [
                Limit::perHour(5)->by($request->ip()),
                Limit::perDay(3)->by(strtolower((string) $request->input('email', ''))),
            ];
        });
    }
}
