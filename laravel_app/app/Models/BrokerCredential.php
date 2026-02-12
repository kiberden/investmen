<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BrokerCredential extends Model
{
    /**
     * Получить связь с брокером.
     */
    public function broker(): BelongsTo
    {
        return $this->belongsTo(Broker::class);
    }
}
