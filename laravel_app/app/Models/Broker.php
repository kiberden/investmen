<?php

declare(strict_types = 1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Модель брокера пользователя с привязкой профиля, credential и аккаунта.
 */
class Broker extends Model
{
    use HasFactory;

    /**
     * Атрибуты для заполнения.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'profile_name',
        'provider_code',
        'description',
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

    /**
     * Получить учетные данные подключения к брокеру.
     */
    public function credential(): HasOne
    {
        return $this->hasOne(BrokerCredential::class);
    }

    /**
     * Получить все аккаунты брокера.
     */
    public function accounts(): HasMany
    {
        return $this->hasMany(Account::class);
    }

    /**
     * Нормализует код провайдера при записи в модель.
     */
    public function setProviderCodeAttribute(mixed $value): void
    {
        $normalized = \is_string($value) ? strtolower(trim($value)) : '';
        $this->attributes['provider_code'] = $normalized !== '' ? $normalized : 'tbank';
    }

    /**
     * Игнорирует транзитное поле token, которое не хранится в brokers.
     */
    public function setTokenAttribute(mixed $value): void
    {
        // Поле token приходит из формы и сохраняется в broker_credentials.
    }

    /**
     * Игнорирует транзитное поле expire_at, которое не хранится в brokers.
     */
    public function setExpireAtAttribute(mixed $value): void
    {
        // Поле expire_at приходит из формы и сохраняется в broker_credentials.
    }
}
