<?php

namespace App\Observers;

use App\Models\Account;

class AccountObserver
{
    /**
     * Автоматически обновляет updated_at при обновлении модели.
     */
    public function updating(Account $account): void
    {
        $account->updated_at = now();
    }
}
