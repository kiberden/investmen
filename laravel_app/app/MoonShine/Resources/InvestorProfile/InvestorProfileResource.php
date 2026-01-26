<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\InvestorProfile;

use App\Models\InvestorProfile;
use App\MoonShine\Resources\InvestorProfile\Pages\InvestorProfileFormPage;
use App\MoonShine\Resources\InvestorProfile\Pages\InvestorProfileIndexPage;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\MenuManager\Attributes\Group;
use MoonShine\MenuManager\Attributes\Order;
use MoonShine\Support\Attributes\Icon;
use MoonShine\Support\Enums\Action;
use MoonShine\Support\ListOf;

/**
 * @extends ModelResource<InvestorProfile, InvestorProfileIndexPage, InvestorProfileFormPage, null>
 */
#[Icon('user-circle')]
#[Group('Инвест. профили')]
#[Order(10)]
class InvestorProfileResource extends ModelResource
{
    protected string $model = InvestorProfile::class;

    protected string $column = 'profile_name';

    protected array $with = ['user'];

    protected bool $simplePaginate = true;

    public function getTitle(): string
    {
        return 'Профили инвесторов';
    }

    protected function activeActions(): ListOf
    {
        return parent::activeActions()->except(Action::VIEW);
    }

    protected function pages(): array
    {
        return [
            InvestorProfileIndexPage::class,
            InvestorProfileFormPage::class,
        ];
    }

    protected function search(): array
    {
        return [
            'id',
            'profile_name',
        ];
    }
}
