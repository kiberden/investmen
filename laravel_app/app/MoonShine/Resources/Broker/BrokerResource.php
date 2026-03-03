<?php

declare(strict_types = 1);

namespace App\MoonShine\Resources\Broker;

use App\Actions\Broker\SyncBrokerCredentialAction;
use App\Models\Broker;
use App\MoonShine\Resources\Broker\Pages\BrokerFormPage;
use App\MoonShine\Resources\Broker\Pages\BrokerIndexPage;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use MoonShine\Contracts\Core\DependencyInjection\FieldsContract;
use MoonShine\Contracts\Core\TypeCasts\DataWrapperContract;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Contracts\UI\FieldContract;
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

    public function save(DataWrapperContract $item, ?FieldsContract $fields = null): DataWrapperContract
    {
        $fields ??= $this->getFormFields()->onlyFields(withApplyWrappers: true);
        /** @var SyncBrokerCredentialAction $syncBrokerCredentialAction */
        $syncBrokerCredentialAction = app(SyncBrokerCredentialAction::class);

        $brokerFields = $fields->exceptElements(
            static fn(ComponentContract $element): bool => $element instanceof FieldContract
            && \in_array($element->getColumn(), SyncBrokerCredentialAction::credentialColumns(), true),
        );

        try {
            return DB::transaction(function () use (
                $item,
                $brokerFields,
                $syncBrokerCredentialAction,
            ): DataWrapperContract {
                $savedItem = parent::save($item, $brokerFields);

                $syncBrokerCredentialAction->execute($savedItem->getOriginal(), request());

                return $savedItem;
            });
        } catch (QueryException $e) {
            if ($this->isTokenUniqueConstraintViolation($e)) {
                throw ValidationException::withMessages([
                    'token' => 'Такой токен уже используется в другом профиле.',
                ]);
            }

            throw $e;
        }
    }

    private function isTokenUniqueConstraintViolation(QueryException $e): bool
    {
        $sqlState = data_get($e->errorInfo, 0);
        $constraintName = data_get($e->errorInfo, 2);

        if ($sqlState !== '23505') {
            return false;
        }

        return \is_string($constraintName)
            && str_contains($constraintName, 'broker_credentials_token_unique');
    }
}
