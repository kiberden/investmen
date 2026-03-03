<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BrokerCredential extends Model
{
    /**
     * Атрибуты для заполнения.
     *
     * @var array<string, string>
     */
    protected $fillable = [
        'broker_id',
        'token',
        'expire_at',
    ];

    /**
     * Атрибуты для каста преобразований.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'token' => 'encrypted',
        'expire_at' => 'datetime',
    ];

    /**
     * Получить связь с брокером.
     */
    public function broker(): BelongsTo
    {
        return $this->belongsTo(Broker::class);
    }
}
