<?php

declare(strict_types = 1);

namespace App\Services\BrokerGateway\Access;

use App\Models\Broker;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Collection;

final class BrokerAccessService
{
    /**
     * @throws AuthorizationException
     */
    public function assertUserCanUseBroker(User $user, Broker $broker): void
    {
        $brokerUserId = (int) $broker->getAttribute('user_id');

        if ($brokerUserId !== (int) $user->getKey()) {
            throw new AuthorizationException('The user does not have access to this broker connection.');
        }
    }

    /**
     * @return Collection<int, Broker>
     */
    public function brokersForUser(User $user): Collection
    {
        return Broker::query()->where('user_id', $user->getKey())->get();
    }
}
