<?php

namespace App\Providers;

use App\Services\Notifications\NotificationServiceInterface;
use App\Services\Notifications\MailNotificationService;
use App\Services\Notifications\TwilioNotificationService;
use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\UrlGenerator;
use Twilio\Rest\Client;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            NotificationServiceInterface::class,
            MailNotificationService::class
        );

        // Enregistrer Twilio
        $this->app->singleton(TwilioNotificationService::class, function ($app) {
            $twilio = new Client(
                config('services.twilio.sid'),
                config('services.twilio.auth_token')
            );
            return new TwilioNotificationService($twilio);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(UrlGenerator $url)
    {
        if (env('APP_ENV') == 'production') {
            $url->forceScheme('https');
        }
    }
}
