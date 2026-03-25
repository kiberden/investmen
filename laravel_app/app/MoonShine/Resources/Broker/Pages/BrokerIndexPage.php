<?php

declare(strict_types = 1);

namespace App\MoonShine\Resources\Broker\Pages;

use App\Models\User;
use App\MoonShine\Resources\Broker\BrokerResource;
use App\MoonShine\Resources\MoonShineUser\MoonShineUserResource;
use Illuminate\Contracts\Database\Eloquent\Builder;
use MoonShine\Contracts\Core\TypeCasts\DataWrapperContract;
use MoonShine\Contracts\UI\ActionButtonContract;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Fields\Relationships\BelongsTo;
use MoonShine\Laravel\Pages\Crud\IndexPage;
use MoonShine\Support\Enums\Ability;
use MoonShine\Support\Enums\Action;
use MoonShine\Support\ListOf;
use MoonShine\UI\Components\ActionButton;
use MoonShine\UI\Components\Table\TableBuilder;
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Switcher;
use MoonShine\UI\Fields\Text;

/**
 * @extends IndexPage<BrokerResource>
 */
final class BrokerIndexPage extends IndexPage
{
    /**
     * @return list<FieldContract>
     */
    protected function fields(): iterable
    {
        return [
            ID::make()->sortable(),
            Text::make('Профиль', 'profile_name')->sortable(),
            Switcher::make('Активен', 'is_active'),
            Text::make('Доступы', 'credential.id')->changePreview(static fn(mixed $value, Text $field): string => $value
                ? 'Настроены'
                : 'Не настроены'),
            Date::make('Действует до', 'credential.expire_at')->format('d.m.Y H:i'),
            Date::make('Создан', 'created_at')->format('d.m.Y')->sortable(),
        ];
    }

    protected function filters(): iterable
    {
        return [
            BelongsTo::make(
                'Пользователь',
                'user',
                formatted: static fn(User $model) => $model->name,
                resource: MoonShineUserResource::class,
            )->valuesQuery(static fn(Builder $q) => $q->select(['id', 'name'])),

            Text::make('Профиль', 'profile_name'),

            Switcher::make('Активен', 'is_active'),
        ];
    }

    /**
     * Кастомная кнопка перехода к портфелям.
     *
     * @return ListOf<ActionButtonContract>
     */
    protected function buttons(): ListOf
    {
        $managementButton = ActionButton::make('К портфелям', fn(
            mixed $item,
            ?DataWrapperContract $data,
        ): string => $this->getResource()->getFormPageUrl($data?->getKey()))
            ->icon('cog-6-tooth')
            ->primary()
            ->canSee(
                fn(mixed $item, ?DataWrapperContract $data): bool => (
                    $data?->getKey() !== null
                    && $this->getResource()->hasAction(Action::UPDATE)
                    && $this->getResource()->setItem($item)->can(Ability::UPDATE)
                ),
            )
            ->showInLine();

        return parent::buttons()->prepend($managementButton);
    }

    /**
     * @param  TableBuilder  $component
     *
     * @return TableBuilder
     */
    protected function modifyListComponent(ComponentContract $component): TableBuilder
    {
        return $component->columnSelection();
    }
}
