<?php

declare(strict_types = 1);

namespace Tests\Unit\BrokerGateway\Providers\TBank\Infrastructure\Rest\Instruments;

use App\Modules\BrokerGateway\Providers\TBank\Infrastructure\Rest\Instruments\EtfRestClient;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

final class EtfRestClientTest extends TestCase
{
    public function test_it_returns_etf_by_id_payload(): void
    {
        Http::fake([
            'https://example.test/rest/*' => Http::response(['instrument' => ['figi' => 'etf-1']], 200),
        ]);

        $payload = app(EtfRestClient::class)->etfBy(
            baseUrl: 'https://example.test/rest',
            token: 'token',
            timeout: 10,
            appName: 'investman-test',
            idType: 'INSTRUMENT_ID_TYPE_FIGI',
            id: 'etf-1',
        );

        $this->assertSame(['instrument' => ['figi' => 'etf-1']], $payload);
    }
}
