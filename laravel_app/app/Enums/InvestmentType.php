<?php

namespace App\Enums;

/**
 * Список типов инвестиций.
 */
enum InvestmentType: string
{
    case STOCKS = 'stocks'; // Акции
    case BONDS = 'bonds'; // Облигации
    case FUNDS = 'funds'; // Фонды
    case CURRENCY = 'currency'; // Валюта

    /**
     * Получить человекочитаемое название типа инвестиции
     */
    public function label(): string
    {
        return match ($this) {
            self::STOCKS => 'Акции',
            self::BONDS => 'Облигации',
            self::FUNDS => 'Фонды',
            self::CURRENCY => 'Валюта',
        };
    }

    /**
     * Получить все доступные типы в виде массива для выбора
     */
    public static function options(): array
    {
        return array_combine(
            array_column(self::cases(), 'value'),
            array_map(fn($case) => $case->label(), self::cases()),
        );
    }

    /**
     * Получить иконку для типа инвестиции (для использования в UI)
     */
    public function icon(): string
    {
        return match ($this) {
            self::STOCKS => 'heroicons.outline.chart-bar',
            self::BONDS => 'heroicons.outline.document-text',
            self::FUNDS => 'heroicons.outline.briefcase',
            self::CURRENCY => 'heroicons.outline.currency-dollar',
        };
    }

    /**
     * Получить цвет для типа инвестиции (для использования в UI)
     */
    public function color(): string
    {
        return match ($this) {
            self::STOCKS => 'blue',
            self::BONDS => 'green',
            self::FUNDS => 'purple',
            self::CURRENCY => 'yellow',
        };
    }
}
