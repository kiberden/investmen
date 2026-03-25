<?php

declare(strict_types = 1);

namespace App\Observers;

use App\Actions\Broker\SyncBrokerCredentialAction;
use App\Models\Broker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

final class BrokerObserver
{
    public function __construct(
        private SyncBrokerCredentialAction $syncBrokerCredentialAction,
    ) {}

    public function creating(Broker $broker): void
    {
        $broker->user_id = Auth::id();
    }

    public function saved(Broker $broker): void
    {
        if (!app()->bound('request')) {
            return;
        }

        /** @var mixed $request */
        $request = app('request');

        if (!$request instanceof Request) {
            return;
        }

        $this->syncBrokerCredentialAction->execute($broker, $request);
    }
}
