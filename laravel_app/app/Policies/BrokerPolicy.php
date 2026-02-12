<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Broker;
use App\Models\User;

class BrokerPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->exists;
    }

    public function view(User $user, Broker $broker): bool
    {
        return $broker->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->exists;
    }

    public function update(User $user, Broker $broker): bool
    {
        return $broker->user_id === $user->id;
    }

    public function delete(User $user, Broker $broker): bool
    {
        return $broker->user_id === $user->id;
    }
}
