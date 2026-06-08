<?php

declare(strict_types = 1);

namespace App\MoonShine\Resources\Broker\Pages;

use App\Models\Broker;
use App\MoonShine\Resources\Broker\BrokerResource;
use App\MoonShine\Resources\Broker\Support\BrokerProviderCodeSupport;
use Illuminate\Validation\Rule;
use MoonShine\Contracts\Core\TypeCasts\DataWrapperContract;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Pages\Crud\FormPage;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Password;
use MoonShine\UI\Fields\Select;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Textarea;

/**
 * Страница создания/редактирования брокера в админке MoonShine.
 *
 * @extends FormPage<BrokerResource, Broker>
 */
final class BrokerFormPage extends FormPage
{
    /**
     * Возвращает набор полей формы брокера, включая provider_code и credential-поля.
     *
     * @return list<ComponentContract|FieldContract>
     */
    protected function fields(): iterable
    {
        $broker = $this->getItem();
        $hasCredential = $broker?->credential !== null;
        $expireAtDefault = $broker?->credential?->expire_at instanceof \DateTimeInterface
            ? $broker?->credential->expire_at->format('Y-m-d H:i:s')
            : null;

        return [
            Box::make([
                ID::make(),
                Text::make('Название профиля', 'profile_name')->required(),
                Select::make('Провайдер', 'provider_code')
                    ->options(BrokerProviderCodeSupport::options())
                    ->default(
                        \is_string($broker?->getAttribute('provider_code')) && trim((string) $broker?->getAttribute('provider_code')) !== ''
                            ? strtolower(trim((string) $broker?->getAttribute('provider_code')))
                            : BrokerProviderCodeSupport::defaultCode(),
                    )
                    ->required()
                    ->hint(
                        BrokerProviderCodeSupport::allowedCodes() === []
                            ? 'Нет доступных провайдеров в config(broker-providers.providers). Сохранение будет отклонено валидацией.'
                            : 'Окружение подключения определяется автоматически через APP_ENV в конфиге провайдера.',
                    ),
                Password::make('Token', 'token')->hint(
                    $hasCredential ? 'Токен сохранен. Оставьте пустым, чтобы не изменять.' : '',
                ),
                Date::make('Активен до', 'expire_at')->default($expireAtDefault),
                Textarea::make('Описание', 'description')->nullable(),
            ]),
        ];
    }

    /**
     * Возвращает правила валидации формы брокера.
     */
    protected function rules(DataWrapperContract $item): array
    {
        $userId = request()->input('user_id') ?? data_get($item->toArray(), 'user_id') ?? $item->getOriginal()->user_id;

        return [
            'profile_name' => [
                'required',
                'max:255',
                Rule::unique(Broker::class, 'profile_name')
                    ->where(static fn($query) => $query->where('user_id', $userId))
                    ->ignoreModel($item->getOriginal()),
            ],
            'description' => ['nullable', 'string'],
            'provider_code' => BrokerProviderCodeSupport::validationRules(),
            'is_active' => ['boolean'],
            'token' => [
                ...( $item->getKey() === null ? ['required'] : ['nullable'] ),
                'string',
            ],
            'expire_at' => ['nullable', 'date'],
        ];
    }
}
