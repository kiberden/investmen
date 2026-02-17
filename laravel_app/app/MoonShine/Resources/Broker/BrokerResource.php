<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Broker;

use App\Models\Broker;
use App\MoonShine\Resources\Broker\Pages\BrokerFormPage;
use App\MoonShine\Resources\Broker\Pages\BrokerIndexPage;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
    private const CREDENTIAL_COLUMNS = [
        'token',
        'secret',
        'expire_at',
    ];

    protected string $model = Broker::class;

    protected bool $withPolicy = true;

    protected string $column = 'profile_name';

    protected array $with = ['user'];

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
        dump($fields);
        $fields ??= $this->getFormFields()->onlyFields(withApplyWrappers: true);
        $credentialData = $this->extractCredentialData($item->toArray());

        $brokerFields = $fields->exceptElements(
            static fn (ComponentContract $element): bool => $element instanceof FieldContract
                && \in_array($element->getColumn(), self::CREDENTIAL_COLUMNS, true)
        );

        return DB::transaction(function () use ($item, $brokerFields, $credentialData): DataWrapperContract {
            $savedItem = parent::save($item, $brokerFields);

            $this->syncCredential($savedItem->getOriginal(), $credentialData);

            return $savedItem;
        });
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function extractCredentialData(array $data): array
    {
        return array_filter(
            [
                'token' => $data['token'] ?? null,
                'secret' => $data['secret'] ?? null,
                'expire_at' => $data['expire_at'] ?? null,
            ],
            static fn (mixed $value): bool => $value !== null && $value !== ''
        );
    }

    /**
     * @param array<string, mixed> $credentialData
     */
    private function syncCredential(Broker $broker, array $credentialData): void
    {
        if ($credentialData === []) {
            return;
        }

        $broker->credential()->updateOrCreate([], $credentialData);
    }
}
