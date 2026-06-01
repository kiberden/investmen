<?php

declare(strict_types = 1);

namespace Tests\Unit\BrokerGateway\Factory;

use App\Services\BrokerGateway\Factory\BrokerGatewayProviderFactory;
use InvalidArgumentException;
use Tests\TestCase;

/**
 * Тесты фабрики провайдера брокерского шлюза.
 */
final class BrokerGatewayProviderFactoryTest extends TestCase
{
    /**
     * Проверяет создание T-Bank провайдера при наличии connection keys.
     */
    public function test_it_creates_tbank_provider_with_connection_keys_present(): void
    {
        config()->set('broker-providers.default_environment', 'sandbox');
        config()->set('broker-providers.providers.tbank', [
            'name' => 'T-Bank',
            'driver' => 'tbank',
            'default_environment' => 'sandbox',
            'default_connection_key' => 'sandbox',
            'connection_keys' => [
                'prod' => [
                    'provider' => 'tbank',
                    'base_url' => 'https://invest-public-api.tbank.ru/rest',
                    'timeout' => 10,
                ],
                'sandbox' => [
                    'provider' => 'tbank',
                    'base_url' => 'https://sandbox-invest-public-api.tbank.ru/rest',
                    'timeout' => 15,
                ],
            ],
        ]);

        $factory = app(BrokerGatewayProviderFactory::class);
        $provider = $factory->make('tbank');

        $this->assertSame('sandbox', $provider->getEnvironment(), 'Factory must keep environment resolution contract.');
        $this->assertSame('https://sandbox-invest-public-api.tbank.ru/rest', $provider->getBaseUrl());
        $this->assertSame(15, $provider->getTimeout());
        $this->assertSame('investman', $provider->getAppName());
    }

    /**
     * Проверяет ошибку при невалидном типе provider-конфига.
     */
    public function test_it_throws_for_invalid_provider_configuration_type(): void
    {
        config()->set('broker-providers.providers.tbank', 'invalid');

        $factory = app(BrokerGatewayProviderFactory::class);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Provider "tbank" is not configured.');

        $factory->make('tbank');
    }

    /**
     * Проверяет ошибку при невалидной структуре connection keys.
     */
    public function test_it_throws_when_connection_keys_structure_is_invalid(): void
    {
        config()->set('broker-providers.default_environment', 'sandbox');
        config()->set('broker-providers.providers.tbank', [
            'name' => 'T-Bank',
            'driver' => 'tbank',
            'default_environment' => 'sandbox',
            'default_connection_key' => 'sandbox',
            'connection_keys' => 'invalid',
        ]);

        $factory = app(BrokerGatewayProviderFactory::class);
        $provider = $factory->make('tbank');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Configuration value "connection_keys" for provider "tbank" must be an array.');

        $provider->getBaseUrl();
    }
}
