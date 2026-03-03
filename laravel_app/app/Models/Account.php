<?php

declare(strict_types = 1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Модель профиля пользователя в брокере.
 */
class Account extends Model
{
    use HasFactory;

    public const CREATED_AT = null;

    /**
     * Атрибуты для заполнения.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'broker_id',
        'name',
    ];

    /**
     * Атрибуты для каста преобразований.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'updated_at' => 'datetime',
    ];

    /**
     * Получить брокера, которому принадлежит аккаунт.
     */
    public function broker(): BelongsTo
    {
        return $this->belongsTo(Broker::class);
    }

    /**
     * Получить портфели аккаунта.
     */
    public function portfolios(): HasMany
    {
        return $this->hasMany(Portfolio::class);
    }
}
