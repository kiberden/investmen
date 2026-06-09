<?php

declare(strict_types = 1);

namespace Tests\Unit\BrokerGateway\Providers\TBank\Infrastructure\Rest;

use App\Modules\BrokerGateway\Providers\TBank\Infrastructure\Rest\TBankBaseRestClient;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

final class TBankBaseRestClientTest extends TestCase
{
    public function test_it_executes_successful_request_with_shared_pipeline(): void
    {
        Http::fake([
            'https://example.test/rest/*' => Http::response(['ok' => true], 200),
        ]);

        $client = new class extends TBankBaseRestClient {
            /**
             * @return array<string, mixed>
             */
            public function execute(string $baseUrl): array
            {
                $response = $this->postJson(
                    $this->buildUrl($baseUrl, 'UsersService', 'GetInfo'),
                    [],
                    'token',
                    10,
                    'investman-test',
                    'connection failed',
                    'request failed',
                );

                $this->assertSuccessful($response, 'failed with status %d');

                return $this->assertArrayPayload($response, 'invalid payload');
            }
        };

        $payload = $client->execute('https://example.test/rest');

        $this->assertSame(['ok' => true], $payload);
    }

    public function test_it_throws_runtime_exception_for_unsuccessful_status(): void
    {
        Http::fake([
            'https://example.test/rest/*' => Http::response(['ok' => false], 500),
        ]);

        $client = new class extends TBankBaseRestClient {
            /**
             * @return array<string, mixed>
             */
            public function execute(string $baseUrl): array
            {
                $response = $this->postJson(
                    $this->buildUrl($baseUrl, 'UsersService', 'GetInfo'),
                    [],
                    'token',
                    10,
                    'investman-test',
                    'connection failed',
                    'request failed',
                );

                $this->assertSuccessful($response, 'failed with status %d');

                return $this->assertArrayPayload($response, 'invalid payload');
            }
        };

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('failed with status 500');

        $client->execute('https://example.test/rest');
    }

    public function test_it_wraps_connection_exceptions_in_runtime_exception(): void
    {
        Http::fake(static function (): void {
            throw new ConnectionException('timeout');
        });

        $client = new class extends TBankBaseRestClient {
            /**
             * @return array<string, mixed>
             */
            public function execute(string $baseUrl): array
            {
                $response = $this->postJson(
                    $this->buildUrl($baseUrl, 'UsersService', 'GetInfo'),
                    [],
                    'token',
                    10,
                    'investman-test',
                    'connection failed',
                    'request failed',
                );

                $this->assertSuccessful($response, 'failed with status %d');

                return $this->assertArrayPayload($response, 'invalid payload');
            }
        };

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('connection failed');

        $client->execute('https://example.test/rest');
    }
}
