<?php

declare(strict_types = 1);

namespace App\Modules\BrokerGateway\Providers\TBank;

use App\Modules\BrokerGateway\Providers\TBank\Infrastructure\Stream\TBankStreamClient;
use Illuminate\Support\ServiceProvider;

/**
 * Провайдер контейнера зависимостей для модуля интеграции T-Bank.
 */
final class TBankModuleServiceProvider extends ServiceProvider
{
    /**
     * Регистрирует сервисы модуля и тегирует провайдера для общего реестра.
     */
    public function register(): void
    {
        $this->app->singleton(TBankStreamClient::class);
        $this->app->singleton(TBankBrokerProvider::class);
        $this->app->tag([TBankBrokerProvider::class], 'broker-gateway.providers');
    }
}
