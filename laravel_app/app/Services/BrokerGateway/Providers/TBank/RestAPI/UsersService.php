<?php

declare(strict_types = 1);

namespace App\Services\BrokerGateway\Providers\TBank\RestAPI;

use App\Modules\BrokerGateway\Providers\TBank\Infrastructure\Rest\TBankUsersRestClient;

/**
 * @deprecated use App\Modules\BrokerGateway\Providers\TBank\Infrastructure\Rest\TBankUsersRestClient
 */
final class UsersService
{
    public function __construct(
        private readonly TBankUsersRestClient $usersRestClient,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function GetInfo(string $baseUrl, #[\SensitiveParameter] string $token, int $timeout, string $appName): array
    {
        return $this->usersRestClient->getInfo(baseUrl: $baseUrl, token: $token, timeout: $timeout, appName: $appName);
    }
}
