<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Broker\Pages;

use App\Models\Broker;
use App\MoonShine\Resources\Broker\BrokerResource;
use Illuminate\Validation\Rule;
use MoonShine\Contracts\Core\TypeCasts\DataWrapperContract;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Pages\Crud\FormPage;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Components\Tabs\Tab;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Password;
use MoonShine\UI\Fields\Textarea;
use MoonShine\UI\Fields\Date;

/**
 * @extends FormPage<BrokerResource, Broker>
 */
final class BrokerFormPage extends FormPage
{
    /**
     * @return list<ComponentContract|FieldContract>
     */
    protected function fields(): iterable
    {
        return [
            Box::make([
                ID::make(),
                Text::make('Название профиля', 'profile_name')
                    ->required(),
                Password::make('Token', 'token'),
                Password::make('Secret', 'secret'),
                Date::make('Активен до', 'expire_at'),
                Textarea::make('Описание', 'description')
                    ->nullable(),
            ]),
        ];
    }

    protected function rules(DataWrapperContract $item): array
    {
        $userId = request()->input('user_id')
            ?? data_get($item->toArray(), 'user_id')
            ?? $item->getOriginal()->user_id;

        return [
            'profile_name' => [
                'required',
                'max:255',
                Rule::unique(Broker::class, 'profile_name')
                    ->where(static fn ($query) => $query->where('user_id', $userId))
                    ->ignoreModel($item->getOriginal()),
            ],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
            'token' => [
                ...$item->getKey() === null ? ['required'] : ['nullable'],
                'string',
            ],
            'secret' => [
                ...$item->getKey() === null ? ['required'] : ['nullable'],
                'string',
            ],
            'expire_at' => ['nullable', 'date'],
        ];
    }
}
