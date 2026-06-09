<?php

declare(strict_types = 1);

namespace Tests\Unit\BrokerGateway\Providers\TBank\Infrastructure\Rest\Instruments;

use App\Modules\BrokerGateway\Providers\TBank\Infrastructure\Rest\Instruments\BondRestClient;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

final class BondRestClientTest extends TestCase
{
    public function test_it_returns_bond_by_id_payload(): void
    {
        Http::fake([
            'https://example.test/rest/*' => Http::response(['instrument' => ['figi' => 'bond-1']], 200),
        ]);

        $payload = app(BondRestClient::class)->bondBy(
            baseUrl: 'https://example.test/rest',
            token: 'token',
            timeout: 10,
            appName: 'investman-test',
            idType: 'INSTRUMENT_ID_TYPE_FIGI',
            id: 'bond-1',
        );

        $this->assertSame(['instrument' => ['figi' => 'bond-1']], $payload);
    }
}
