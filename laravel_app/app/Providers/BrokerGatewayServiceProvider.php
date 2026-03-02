<?php

declare(strict_types = 1);

namespace App\Providers;

use App\Services\BrokerGateway\Access\BrokerAccessService;
use App\Services\BrokerGateway\Credentials\BrokerCredentialResolver;
use App\Services\BrokerGateway\Factory\BrokerGatewayProviderFactory;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\ServiceProvider;

final class BrokerGatewayServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(config_path('broker-providers.php'), 'broker-providers');

        $this->app->singleton(
            BrokerGatewayProviderFactory::class,
            static fn(Container $container): BrokerGatewayProviderFactory => new BrokerGatewayProviderFactory(
                $container->make(Repository::class),
                $container,
            ),
        );

        $this->app->singleton(BrokerAccessService::class);
        $this->app->singleton(BrokerCredentialResolver::class);
    }
}
