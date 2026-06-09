<?php

declare(strict_types = 1);

namespace Tests\Unit\BrokerGateway\Providers\TBank\Infrastructure\Rest\Instruments;

use App\Modules\BrokerGateway\Providers\TBank\Infrastructure\Rest\Instruments\ShareRestClient;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

final class ShareRestClientTest extends TestCase
{
    public function test_it_returns_share_by_id_payload(): void
    {
        Http::fake([
            'https://example.test/rest/*' => Http::response(['instrument' => ['figi' => 'share-1']], 200),
        ]);

        $payload = app(ShareRestClient::class)->shareBy(
            baseUrl: 'https://example.test/rest',
            token: 'token',
            timeout: 10,
            appName: 'investman-test',
            idType: 'INSTRUMENT_ID_TYPE_FIGI',
            id: 'share-1',
        );

        $this->assertSame(['instrument' => ['figi' => 'share-1']], $payload);
    }
}
