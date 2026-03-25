<?php

declare(strict_types = 1);

namespace App\MoonShine\Resources\Account\Pages;

use App\MoonShine\Resources\Account\AccountResource;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Pages\Crud\IndexPage;
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Text;

/**
 * @extends IndexPage<AccountResource>
 */
final class AccountIndexPage extends IndexPage
{
    /**
     * @return list<FieldContract>
     */
    protected function fields(): iterable
    {
        return [
            ID::make()->sortable(),
            Text::make('Название счета', 'name')->sortable(),
            Text::make('Брокер', 'broker.profile_name')->sortable(),
            Date::make('Обновлен', 'updated_at')->format('d.m.Y H:i')->sortable(),
        ];
    }

    protected function filters(): iterable
    {
        return [];
    }
}
