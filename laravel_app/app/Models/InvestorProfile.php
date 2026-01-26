<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvestorProfile extends Model
{
    use HasFactory;

    /**
     * Атрибуты для заполнения.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'profile_name',
        'total_capital',
        'description',
        'is_active',
    ];

    /**
     * Атрибуты для каста преобразований.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'total_capital' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Получить связь с профилем пользователя.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
