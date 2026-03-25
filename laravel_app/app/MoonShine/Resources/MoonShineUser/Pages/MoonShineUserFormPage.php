<?php

declare(strict_types = 1);

namespace App\MoonShine\Resources\MoonShineUser\Pages;

use App\Models\User as MoonshineUser;
use App\MoonShine\Resources\MoonShineUser\MoonShineUserResource;
use App\MoonShine\Resources\MoonShineUserRole\MoonShineUserRoleResource;
use App\Services\BrokerGateway\Profiles\BrokerProfileService;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password as PasswordRule;
use MoonShine\Contracts\Core\TypeCasts\DataWrapperContract;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Fields\Relationships\BelongsTo;
use MoonShine\Laravel\Models\MoonshineUserRole;
use MoonShine\Laravel\Pages\Crud\FormPage;
use MoonShine\UI\Components\Alert;
use MoonShine\UI\Components\Collapse;
use MoonShine\UI\Components\Heading;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Components\Layout\Flex;
use MoonShine\UI\Components\Table\TableBuilder;
use MoonShine\UI\Components\Tabs;
use MoonShine\UI\Components\Tabs\Tab;
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\Email;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Image;
use MoonShine\UI\Fields\Password;
use MoonShine\UI\Fields\PasswordRepeat;
use MoonShine\UI\Fields\Text;
use Throwable;

/**
 * @extends FormPage<MoonShineUserResource, MoonshineUser>
 */
final class MoonShineUserFormPage extends FormPage
{
    /**
     * @return list<ComponentContract|FieldContract>
     */
    protected function fields(): iterable
    {
        return [
            Box::make([
                Tabs::make([
                    Tab::make(__('moonshine::ui.resource.main_information'), [
                        ID::make(),

                        BelongsTo::make(
                            __('moonshine::ui.resource.role'),
                            'moonshineUserRole',
                            formatted: static fn(MoonshineUserRole $model) => $model->name,
                            resource: MoonShineUserRoleResource::class,
                        )
                            ->creatable()
                            ->valuesQuery(static fn(Builder $q) => $q->select(['id', 'name'])),

                        Flex::make([
                            Text::make(__('moonshine::ui.resource.name'), 'name')->required(),

                            Email::make(__('moonshine::ui.resource.email'), 'email')->required(),
                        ]),

                        Image::make(__('moonshine::ui.resource.avatar'), 'avatar')
                            ->disk(moonshineConfig()->getDisk())
                            ->dir(moonshineConfig()->getUserAvatarsDir())
                            ->allowedExtensions(['jpg', 'png', 'jpeg', 'gif']),

                        Date::make(__('moonshine::ui.resource.created_at'), 'created_at')
                            ->format('d.m.Y')
                            ->default(now()->toDateTimeString()),
                    ])->icon('user-circle'),

                    Tab::make(__('moonshine::ui.resource.password'), [
                        Collapse::make(__('moonshine::ui.resource.change_password'), [
                            Password::make(__('moonshine::ui.resource.password'), 'password')->customAttributes([
                                'autocomplete' => 'new-password',
                            ])->eye(),

                            PasswordRepeat::make(
                                __('moonshine::ui.resource.repeat_password'),
                                'password_confirmation',
                            )->customAttributes(['autocomplete' => 'confirm-password'])->eye(),
                        ])->icon('lock-closed'),
                    ])->icon('lock-closed'),
                    Tab::make('Профили брокера', $this->brokerProfilesTab()),
                ]),
            ]),
        ];
    }

    /**
     * @return list<ComponentContract>
     */
    private function brokerProfilesTab(): array
    {
        /** @var MoonshineUser|null $user */
        $user = $this->getItem();

        if (!$user instanceof MoonshineUser || $user->getKey() === null) {
            return [
                Alert::make(type: 'info')->content('Сохраните пользователя, чтобы загрузить профили брокера.'),
            ];
        }

        try {
            $profiles = app(BrokerProfileService::class)->profilesForUser($user);
        } catch (Throwable $e) {
            return [
                Alert::make(type: 'warning')->content($e->getMessage()),
            ];
        }

        if ($profiles === []) {
            return [
                Alert::make(type: 'info')->content('У пользователя нет подключенных брокерских профилей.'),
            ];
        }

        return [
            Heading::make('Профили, полученные из REST брокера'),
            TableBuilder::make([
                Text::make('Профиль в системе', 'broker_profile_name'),
                Text::make('Broker ID', 'broker_id'),
                Text::make('Провайдер', 'provider'),
                Text::make('Окружение', 'environment'),
                Text::make('Account ID', 'account_id'),
                Text::make('Название', 'account_name'),
                Text::make('Тип', 'account_type'),
                Text::make('Статус', 'account_status'),
                Text::make('Доступ', 'access_level'),
                Text::make('Открыт', 'opened_at'),
                Text::make('Закрыт', 'closed_at'),
                Text::make('Ошибка', 'error'),
            ], $profiles),
        ];
    }

    protected function rules(DataWrapperContract $item): array
    {
        return [
            'name' => 'required',
            'moonshine_user_role_id' => 'required',
            'email' => [
                'sometimes',
                'bail',
                'required',
                'email',
                Rule::unique($item->getOriginal()::class)->ignoreModel($item->getOriginal()),
            ],
            'avatar' => ['sometimes', 'nullable', 'image', 'mimes:jpeg,jpg,png,gif'],
            'password' => [
                ...( $item->getKey() !== null ? ['sometimes', 'nullable'] : ['required'] ),
                PasswordRule::defaults(),
                'confirmed',
            ],
        ];
    }
}
