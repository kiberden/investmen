<?php

namespace App\Observers;

use App\Models\InvestorProfile;
use Illuminate\Support\Facades\Auth;

class InvestorProfileObserver
{
    /**
     * @param InvestorProfile $investorProfile
     * @return void
     */
    public function creating(InvestorProfile $investorProfile): void
    {
        $investorProfile->user_id = Auth::id();
    }
}
