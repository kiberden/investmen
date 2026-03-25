<?php

declare(strict_types = 1);

namespace Tests\Unit\BrokerGateway\Providers\TBank\Infrastructure\Rest;

use App\Modules\BrokerGateway\Providers\TBank\Infrastructure\Rest\TBankUsersRestClient;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

final class TBankUsersRestClientTest extends TestCase
{
    public function test_it_returns_payload_for_successful_response(): void
    {
        Http::fake([
            'https://example.test/rest/*' => Http::response(['name' => 'Investor'], 200),
        ]);

        $client = app(TBankUsersRestClient::class);
        $payload = $client->getInfo('https://example.test/rest', 'token', 10, 'investman-test');

        $this->assertSame(['name' => 'Investor'], $payload);
    }

    public function test_it_throws_runtime_exception_for_unsuccessful_status(): void
    {
        Http::fake([
            'https://example.test/rest/*' => Http::response(['message' => 'error'], 500),
        ]);

        $client = app(TBankUsersRestClient::class);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('T-Bank UsersService/GetInfo failed with status 500.');

        $client->getInfo('https://example.test/rest', 'token', 10, 'investman-test');
    }

    public function test_it_throws_runtime_exception_for_connection_errors(): void
    {
        Http::fake(static function (): void {
            throw new ConnectionException('timeout');
        });

        $client = app(TBankUsersRestClient::class);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Failed to connect to T-Bank UsersService/GetInfo endpoint.');

        $client->getInfo('https://example.test/rest', 'token', 10, 'investman-test');
    }
}
