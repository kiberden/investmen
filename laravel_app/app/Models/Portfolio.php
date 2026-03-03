<?php

declare(strict_types = 1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Модель портфеля для связи аккаунта с набором позиций.
 */
class Portfolio extends Model
{
    use HasFactory;

    public const CREATED_AT = null;

    /**
     * Атрибуты для заполнения.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'account_id',
        'name',
        'is_active',
    ];

    /**
     * Атрибуты для каста преобразований.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'updated_at' => 'datetime',
    ];

    /**
     * Получить аккаунт, к которому относится портфель.
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
