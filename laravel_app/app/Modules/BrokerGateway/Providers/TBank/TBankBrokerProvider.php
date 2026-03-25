<?php

declare(strict_types = 1);

namespace App\Modules\BrokerGateway\Providers\TBank;

use App\Models\Broker;
use App\Modules\BrokerGateway\Core\Contracts\BrokerProvider;
use App\Modules\BrokerGateway\Core\Contracts\BrokerStreamClient;
use App\Modules\BrokerGateway\Core\DTO\SyncBrokerAccountResult;
use App\Modules\BrokerGateway\Providers\TBank\Application\SyncTBankAccountAction;
use App\Modules\BrokerGateway\Providers\TBank\Infrastructure\Stream\TBankStreamClient;

/**
 * Реализация провайдера брокерского шлюза для T-Bank.
 */
final readonly class TBankBrokerProvider implements BrokerProvider
{
    /**
     * @param SyncTBankAccountAction $syncAccountAction Сценарий синхронизации аккаунта.
     * @param TBankStreamClient $streamClient Клиент потокового подключения T-Bank.
     */
    public function __construct(
        private SyncTBankAccountAction $syncAccountAction,
        private TBankStreamClient $streamClient,
    ) {}

    /**
     * Возвращает код провайдера для реестра модулей.
     */
    public function code(): string
    {
        return 'tbank';
    }

    /**
     * Синхронизирует имя аккаунта брокера через интеграцию T-Bank.
     */
    public function syncAccount(Broker $broker): SyncBrokerAccountResult
    {
        return $this->syncAccountAction->execute($broker);
    }

    /**
     * Возвращает stream-клиент провайдера T-Bank.
     */
    public function streamClient(): BrokerStreamClient
    {
        return $this->streamClient;
    }
}
