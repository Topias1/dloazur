<?php

namespace App\Providers;

use Illuminate\Mail\MailManager;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\Mailer\Bridge\Brevo\Transport\BrevoTransportFactory;
use Symfony\Component\Mailer\Transport\Dsn;

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
        if ($this->app->environment('production')) {
            URL::forceHttps();
        }

        Vite::prefetch(concurrency: 3);

        // Brevo Symfony mailer transport — wired here so config/mail.php can
        // reference the "brevo" transport key cleanly. DSN: brevo+api://KEY@default.
        $this->app->resolving(MailManager::class, function (MailManager $manager): void {
            $manager->extend('brevo', function (array $config) {
                $key = config('services.brevo.key', '');

                return (new BrevoTransportFactory())->create(
                    Dsn::fromString("brevo+api://{$key}@default")
                );
            });
        });
    }
}
