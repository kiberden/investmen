<?php

declare(strict_types = 1);

namespace App\Modules\BrokerGateway\Providers\TBank\Infrastructure\Stream;

use App\Modules\BrokerGateway\Core\Contracts\BrokerStreamClient;
use Illuminate\Support\Facades\Log;

/**
 * Клиент потокового подключения к T-Bank.
 *
 * Текущая реализация является заглушкой и используется как точка расширения
 * для будущей интеграции сокетов/стриминга.
 */
final class TBankStreamClient implements BrokerStreamClient
{
    /**
     * Запускает обработчик стрима T-Bank.
     */
    public function run(): void
    {
        Log::info('T-Bank stream client skipped: stream integration is not implemented for MVP-1.');
    }
}
