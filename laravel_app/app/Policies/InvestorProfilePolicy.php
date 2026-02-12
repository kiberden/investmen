<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\InvestorProfile;
use App\Models\User;

class InvestorProfilePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->exists;
    }

    public function view(User $user, InvestorProfile $investorProfile): bool
    {
        return $investorProfile->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->exists;
    }

    public function update(User $user, InvestorProfile $investorProfile): bool
    {
        return $investorProfile->user_id === $user->id;
    }

    public function delete(User $user, InvestorProfile $investorProfile): bool
    {
        return $investorProfile->user_id === $user->id;
    }
}
