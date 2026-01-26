<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\InvestorProfile\Pages;

use App\Models\User;
use App\MoonShine\Resources\InvestorProfile\InvestorProfileResource;
use App\MoonShine\Resources\MoonShineUser\MoonShineUserResource;
use Illuminate\Contracts\Database\Eloquent\Builder;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Fields\Relationships\BelongsTo;
use MoonShine\Laravel\Pages\Crud\IndexPage;
use MoonShine\UI\Components\Table\TableBuilder;
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Switcher;
use MoonShine\UI\Fields\Text;

/**
 * @extends IndexPage<InvestorProfileResource>
 */
final class InvestorProfileIndexPage extends IndexPage
{
    /**
     * @return list<FieldContract>
     */
    protected function fields(): iterable
    {
        return [
            ID::make()->sortable(),

            BelongsTo::make(
                'Пользователь',
                'user',
                formatted: static fn (User $model) => $model->name,
                resource: MoonShineUserResource::class,
            ),

            Text::make('Профиль', 'profile_name')
                ->sortable(),

            Number::make('Капитал', 'total_capital')
                ->sortable(),

            Switcher::make('Активен', 'is_active'),

            Date::make('Создан', 'created_at')
                ->format('d.m.Y')
                ->sortable(),
        ];
    }

    protected function filters(): iterable
    {
        return [
            BelongsTo::make(
                'Пользователь',
                'user',
                formatted: static fn (User $model) => $model->name,
                resource: MoonShineUserResource::class,
            )->valuesQuery(static fn (Builder $q) => $q->select(['id', 'name'])),

            Text::make('Профиль', 'profile_name'),

            Switcher::make('Активен', 'is_active'),
        ];
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
