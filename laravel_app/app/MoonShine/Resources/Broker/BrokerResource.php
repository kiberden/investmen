<?php

declare(strict_types = 1);

namespace App\MoonShine\Resources\Broker;

use App\Models\Broker;
use App\MoonShine\Resources\Broker\Pages\BrokerFormPage;
use App\MoonShine\Resources\Broker\Pages\BrokerIndexPage;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\MenuManager\Attributes\Group;
use MoonShine\MenuManager\Attributes\Order;
use MoonShine\Support\Attributes\Icon;
use MoonShine\Support\Enums\Action;
use MoonShine\Support\ListOf;

/**
 * @extends ModelResource<Broker, BrokerIndexPage, BrokerFormPage, null>
 */
#[Icon('user-circle')]
#[Group('Инвестиции')]
#[Order(10)]
class BrokerResource extends ModelResource
{
    protected string $model = Broker::class;

    protected bool $withPolicy = true;

    protected string $column = 'profile_name';

    protected array $with = ['user', 'credential'];

    protected bool $simplePaginate = true;

    public function getTitle(): string
    {
        return 'Брокеры';
    }

    protected function activeActions(): ListOf
    {
        return parent::activeActions()->except(Action::VIEW);
    }

    protected function pages(): array
    {
        return [
            BrokerIndexPage::class,
            BrokerFormPage::class,
        ];
    }

    protected function search(): array
    {
        return ['id', 'profile_name'];
    }

    protected function modifyQueryBuilder(Builder $eloquentBuilder): Builder
    {
        /** @var \Illuminate\Database\Eloquent\Builder $eloquentBuilder */
        return $eloquentBuilder->where('user_id', Auth::id());
    }

    protected function modifyItemQueryBuilder(Builder $eloquentBuilder): Builder
    {
        /** @var \Illuminate\Database\Eloquent\Builder $eloquentBuilder */
        return $eloquentBuilder->where('user_id', Auth::id());
    }
}
