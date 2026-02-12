<?php

namespace App\Observers;

use App\Models\Broker;
use Illuminate\Support\Facades\Auth;

class BrokerObserver
{
    /**
     * @param Broker $broker
     * @return void
     */
    public function creating(Broker $broker): void
    {
        $broker->user_id = Auth::id();
    }
}
