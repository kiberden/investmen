<?php

declare(strict_types = 1);

namespace App\Providers;

use App\Modules\BrokerGateway\Core\BrokerApiSettingsResolver;
use App\Modules\BrokerGateway\Core\BrokerProviderCodeResolver;
use App\Modules\BrokerGateway\Core\BrokerProviderRegistry;
use App\Modules\BrokerGateway\Providers\TBank\TBankModuleServiceProvider;
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
        $this->mergeConfigFrom(config_path('broker-systems.php'), 'broker-systems');
        $this->mergeConfigFrom(config_path('broker-providers.php'), 'broker-providers');
        $this->app->register(TBankModuleServiceProvider::class);

        $this->app->singleton(
            BrokerGatewayProviderFactory::class,
            static fn(Container $container): BrokerGatewayProviderFactory => new BrokerGatewayProviderFactory(
                $container->make(Repository::class),
                $container,
            ),
        );
        $this->app->singleton(
            BrokerProviderCodeResolver::class,
            static fn(Container $container): BrokerProviderCodeResolver => new BrokerProviderCodeResolver(
                $container->make(Repository::class),
            ),
        );
        $this->app->singleton(
            BrokerApiSettingsResolver::class,
            static fn(Container $container): BrokerApiSettingsResolver => new BrokerApiSettingsResolver(
                $container->make(BrokerProviderCodeResolver::class),
                $container->make(BrokerGatewayProviderFactory::class),
            ),
        );

        $this->app->singleton(
            BrokerProviderRegistry::class,
            fn(Container $container): BrokerProviderRegistry => new BrokerProviderRegistry($container->tagged(
                'broker-gateway.providers',
            )),
        );

        $this->app->singleton(BrokerAccessService::class);
        $this->app->singleton(BrokerCredentialResolver::class);
    }
}
