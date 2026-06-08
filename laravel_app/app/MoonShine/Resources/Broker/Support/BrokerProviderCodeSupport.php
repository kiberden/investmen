<?php

declare(strict_types = 1);

namespace App\MoonShine\Resources\Broker\Support;

use Closure;
use Illuminate\Validation\Rule;

/**
 * Утилита для подготовки provider_code опций и правил валидации для форм брокера.
 */
final class BrokerProviderCodeSupport
{
    /**
     * Формирует список опций провайдера для select-поля.
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        /** @var mixed $providers */
        $providers = config('broker-providers.providers', []);

        if (!\is_array($providers)) {
            return [];
        }

        $options = [];

        foreach ($providers as $providerCode => $providerConfig) {
            if (!\is_string($providerCode) || trim($providerCode) === '') {
                continue;
            }

            $normalizedProviderCode = strtolower(trim($providerCode));
            $providerName = \is_array($providerConfig) && \is_string($providerConfig['name'] ?? null)
                ? trim((string) $providerConfig['name'])
                : '';

            $options[$normalizedProviderCode] = $providerName !== ''
                ? sprintf('%s (%s)', $providerName, $normalizedProviderCode)
                : $normalizedProviderCode;
        }

        return $options;
    }

    /**
     * Возвращает список допустимых provider_code для валидации.
     *
     * @return list<string>
     */
    public static function allowedCodes(): array
    {
        return array_keys(self::options());
    }

    /**
     * Возвращает дефолтный provider_code для create-формы.
     */
    public static function defaultCode(): string
    {
        $allowedCodes = self::allowedCodes();

        if (\in_array('tbank', $allowedCodes, true)) {
            return 'tbank';
        }

        return $allowedCodes[0] ?? 'tbank';
    }

    /**
     * Возвращает набор правил валидации поля provider_code.
     *
     * @return array<int, string|Rule|Closure>
     */
    public static function validationRules(): array
    {
        $allowedCodes = self::allowedCodes();
        $rules = ['required', 'string'];

        if ($allowedCodes !== []) {
            $rules[] = Rule::in($allowedCodes);
        }

        $rules[] = static function (string $attribute, mixed $value, Closure $fail): void {
            if (self::allowedCodes() === []) {
                $fail('No broker providers configured for selection.');
            }
        };

        return $rules;
    }
}
