<?php

namespace App\Providers;

use App\Listeners\RecordNotificationDelivery;
use App\Support\ClinicSettings;
use Illuminate\Notifications\Events\NotificationFailed;
use Illuminate\Notifications\Events\NotificationSent;
use Illuminate\Support\Facades\Event;
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
        /*
         * Delivery logging.
         *
         * Registered explicitly rather than left to Laravel's listener
         * auto-discovery, which only finds classes with a handle() or
         * __invoke() method. This listener has two methods because it handles
         * two different events, and a single handle() taking a union type
         * would be less readable than the pair.
         *
         * The listener writes to notification_logs, which is the only record
         * that a message the clinic believes it sent actually left the
         * building. See App\Services\Notifications\AppointmentNotifier.
         */
        Event::listen(NotificationSent::class, [RecordNotificationDelivery::class, 'sent']);
        Event::listen(NotificationFailed::class, [RecordNotificationDelivery::class, 'failed']);

        /*
         * Staff-editable settings, written over the config defaults.
         *
         * Applied here rather than read at each call site so that every
         * existing config('clinic.…') caller — the availability engine, the
         * Contact helper, the mail templates — picks up a change made in the
         * panel without being rewritten. See App\Support\ClinicSettings.
         */
        ClinicSettings::apply();
    }
}
