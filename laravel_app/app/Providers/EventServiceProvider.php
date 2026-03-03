<?php

namespace App\Providers;

use App\Models\Account;
use App\Models\Broker;
use App\Models\Portfolio;
use App\Observers\AccountObserver;
use App\Observers\BrokerObserver;
use App\Observers\PortfolioObserver;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        Account::observe(AccountObserver::class);
        Broker::observe(BrokerObserver::class);
        Portfolio::observe(PortfolioObserver::class);
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
