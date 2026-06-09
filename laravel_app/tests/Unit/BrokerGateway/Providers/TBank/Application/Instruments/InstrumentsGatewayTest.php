<?php

declare(strict_types = 1);

namespace Tests\Unit\BrokerGateway\Providers\TBank\Application\Instruments;

use App\Modules\BrokerGateway\Providers\TBank\Application\Instruments\InstrumentsGateway;
use RuntimeException;
use Tests\TestCase;

final class InstrumentsGatewayTest extends TestCase
{
    public function test_it_throws_runtime_exception_for_unsupported_kind(): void
    {
        $gateway = app(InstrumentsGateway::class);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unsupported instrument kind: crypto');

        $gateway->getInstrumentById(
            kind: 'crypto',
            idType: 'INSTRUMENT_ID_TYPE_FIGI',
            id: 'btc',
            context: [
                'base_url' => 'https://example.test/rest',
                'token' => 'token',
                'timeout' => 10,
                'app_name' => 'investman-test',
            ],
        );
    }
}
