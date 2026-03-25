<?php

declare(strict_types = 1);

namespace App\MoonShine\Pages;

use App\Models\User;
use App\Services\BrokerGateway\Profiles\BrokerProfileService;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Laravel\MoonShineAuth;
use MoonShine\UI\Components\Alert;
use MoonShine\UI\Components\Heading;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Components\Table\TableBuilder;
use MoonShine\UI\Fields\Text;
use Throwable;

final class ProfilePage extends \MoonShine\Laravel\Pages\ProfilePage
{
    /**
     * @return list<ComponentContract>
     */
    protected function components(): iterable
    {
        return [
            ...parent::components(),
            $this->brokerProfilesBox(),
        ];
    }

    private function brokerProfilesBox(): ComponentContract
    {
        $user = MoonShineAuth::getGuard()->user();

        if (!$user instanceof User) {
            return Box::make([
                Alert::make(type: 'warning')->content(
                    'Не удалось определить пользователя для загрузки данных брокера.',
                ),
            ]);
        }

        try {
            $profiles = app(BrokerProfileService::class)->profilesForUser($user);
        } catch (Throwable $e) {
            return Box::make([
                Heading::make('Профили брокера'),
                Alert::make(type: 'warning')->content($e->getMessage()),
            ]);
        }

        if ($profiles === []) {
            return Box::make([
                Heading::make('Профили брокера'),
                Alert::make(type: 'info')->content('У пользователя нет подключенных брокерских профилей.'),
            ]);
        }

        return Box::make([
            Heading::make('Профили брокера'),
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
        ]);
    }
}
