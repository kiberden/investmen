<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\InvestorProfile\Pages;

use App\Models\InvestorProfile;
use App\Models\User;
use App\MoonShine\Resources\InvestorProfile\InvestorProfileResource;
use App\MoonShine\Resources\MoonShineUser\MoonShineUserResource;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;
use MoonShine\Contracts\Core\TypeCasts\DataWrapperContract;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Fields\Relationships\BelongsTo;
use MoonShine\Laravel\Pages\Crud\FormPage;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Switcher;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Textarea;

/**
 * @extends FormPage<InvestorProfileResource, InvestorProfile>
 */
final class InvestorProfileFormPage extends FormPage
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
                Rule::unique(InvestorProfile::class, 'profile_name')
                    ->where(static fn ($query) => $query->where('user_id', $userId))
                    ->ignoreModel($item->getOriginal()),
            ],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ];
    }
}
