<?php

declare(strict_types = 1);

namespace App\Modules\BrokerGateway\Providers\TBank;

use App\Modules\BrokerGateway\Providers\TBank\Application\FetchTBankOperationsByCursorAction;
use App\Modules\BrokerGateway\Providers\TBank\Application\FetchTBankPortfolioAction;
use App\Modules\BrokerGateway\Providers\TBank\Application\FetchTBankPositionsAction;
use App\Modules\BrokerGateway\Providers\TBank\Application\FetchTBankInstrumentByIdAction;
use App\Modules\BrokerGateway\Providers\TBank\Application\Instruments\InstrumentsGateway;
use App\Modules\BrokerGateway\Providers\TBank\Application\ListTBankInstrumentsAction;
use App\Modules\BrokerGateway\Providers\TBank\Application\SyncTBankBrokerAccountsAction;
use App\Modules\BrokerGateway\Providers\TBank\Infrastructure\Rest\Instruments\AssetRestClient;
use App\Modules\BrokerGateway\Providers\TBank\Infrastructure\Rest\Instruments\BondRestClient;
use App\Modules\BrokerGateway\Providers\TBank\Infrastructure\Rest\Instruments\EtfRestClient;
use App\Modules\BrokerGateway\Providers\TBank\Infrastructure\Rest\Instruments\ShareRestClient;
use App\Modules\BrokerGateway\Providers\TBank\Infrastructure\Rest\TBankOperationsRestClient;
use App\Modules\BrokerGateway\Providers\TBank\Infrastructure\Stream\TBankStreamClient;
use App\Services\BrokerGateway\InstrumentsService;
use App\Services\BrokerGateway\PortfolioService;
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
        $this->app->singleton(TBankOperationsRestClient::class);
        $this->app->singleton(FetchTBankPortfolioAction::class);
        $this->app->singleton(FetchTBankPositionsAction::class);
        $this->app->singleton(FetchTBankOperationsByCursorAction::class);
        $this->app->singleton(SyncTBankBrokerAccountsAction::class);
        $this->app->singleton(AssetRestClient::class);
        $this->app->singleton(BondRestClient::class);
        $this->app->singleton(EtfRestClient::class);
        $this->app->singleton(ShareRestClient::class);
        $this->app->singleton(InstrumentsGateway::class);
        $this->app->singleton(FetchTBankInstrumentByIdAction::class);
        $this->app->singleton(ListTBankInstrumentsAction::class);
        $this->app->singleton(PortfolioService::class);
        $this->app->singleton(InstrumentsService::class);
        $this->app->singleton(TBankStreamClient::class);
        $this->app->singleton(TBankBrokerProvider::class);
        $this->app->tag([TBankBrokerProvider::class], 'broker-gateway.providers');
    }
}
