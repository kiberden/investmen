<?php

declare(strict_types = 1);

namespace Tests\Unit\BrokerGateway\Providers\TBank\Infrastructure\Rest;

use App\Modules\BrokerGateway\Providers\TBank\Infrastructure\Rest\TBankOperationsRestClient;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

final class TBankOperationsRestClientTest extends TestCase
{
    public function test_it_returns_portfolio_payload(): void
    {
        Http::fake([
            'https://example.test/rest/*' => Http::response(['positions' => []], 200),
        ]);

        $payload = app(TBankOperationsRestClient::class)->getPortfolio(
            baseUrl: 'https://example.test/rest',
            token: 'token',
            timeout: 10,
            appName: 'investman-test',
            accountId: 'acc-1',
        );

        $this->assertSame(['positions' => []], $payload);
    }

    public function test_it_throws_runtime_exception_for_failed_positions_request(): void
    {
        Http::fake([
            'https://example.test/rest/*' => Http::response(['message' => 'error'], 500),
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('T-Bank OperationsService/GetPositions failed with status 500.');

        app(TBankOperationsRestClient::class)->getPositions(
            baseUrl: 'https://example.test/rest',
            token: 'token',
            timeout: 10,
            appName: 'investman-test',
            accountId: 'acc-1',
        );
    }

    public function test_it_sends_cursor_request_payload(): void
    {
        Http::fake([
            'https://example.test/rest/*' => Http::response(['items' => [], 'hasNext' => false], 200),
        ]);

        $payload = app(TBankOperationsRestClient::class)->getOperationsByCursor(
            baseUrl: 'https://example.test/rest',
            token: 'token',
            timeout: 10,
            appName: 'investman-test',
            request: [
                'accountId' => 'acc-1',
                'cursor' => 'cursor-1',
                'limit' => 100,
            ],
        );

        $this->assertSame(['items' => [], 'hasNext' => false], $payload);
    }
}
