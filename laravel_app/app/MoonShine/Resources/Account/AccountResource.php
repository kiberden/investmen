<?php

declare(strict_types = 1);

namespace App\MoonShine\Resources\Account;

use App\Models\Account;
use App\MoonShine\Resources\Account\Pages\AccountIndexPage;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\MenuManager\Attributes\Group;
use MoonShine\MenuManager\Attributes\Order;
use MoonShine\Support\Attributes\Icon;

/**
 * @extends ModelResource<Account, AccountIndexPage, null, null>
 */
#[Icon('wallet')]
#[Group('Инвестиции')]
#[Order(20)]
final class AccountResource extends ModelResource
{
    protected string $model = Account::class;

    protected bool $withPolicy = true;

    protected string $column = 'name';

    protected array $with = ['broker'];

    protected bool $simplePaginate = true;

    public function getTitle(): string
    {
        return 'Счета';
    }

    protected function pages(): array
    {
        return [
            AccountIndexPage::class,
        ];
    }

    protected function search(): array
    {
        return ['id', 'name'];
    }

    protected function modifyQueryBuilder(Builder $eloquentBuilder): Builder
    {
        /** @var \Illuminate\Database\Eloquent\Builder $eloquentBuilder */
        return $eloquentBuilder->whereHas('broker', static fn(Builder $query): Builder => $query->where(
            'user_id',
            Auth::id(),
        ));
    }

    protected function modifyItemQueryBuilder(Builder $eloquentBuilder): Builder
    {
        /** @var \Illuminate\Database\Eloquent\Builder $eloquentBuilder */
        return $eloquentBuilder->whereHas('broker', static fn(Builder $query): Builder => $query->where(
            'user_id',
            Auth::id(),
        ));
    }
}
