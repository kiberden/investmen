<?php

declare(strict_types = 1);

namespace Tests\Unit\BrokerGateway\Providers\TBank\Infrastructure\Rest\Instruments;

use App\Modules\BrokerGateway\Providers\TBank\Infrastructure\Rest\Instruments\AssetRestClient;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

final class AssetRestClientTest extends TestCase
{
    public function test_it_returns_asset_by_id_payload(): void
    {
        Http::fake([
            'https://example.test/rest/*' => Http::response(['asset' => ['id' => 'asset-1']], 200),
        ]);

        $payload = app(AssetRestClient::class)->getAssetBy(
            baseUrl: 'https://example.test/rest',
            token: 'token',
            timeout: 10,
            appName: 'investman-test',
            id: 'asset-1',
        );

        $this->assertSame(['asset' => ['id' => 'asset-1']], $payload);
    }
}
