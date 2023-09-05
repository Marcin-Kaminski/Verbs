<?php

declare(strict_types=1);

namespace App\Orchid;

use Orchid\Platform\Dashboard;
use Orchid\Platform\ItemPermission;
use Orchid\Platform\OrchidServiceProvider;
use Orchid\Screen\Actions\Menu;
use Orchid\Support\Color;

class PlatformProvider extends OrchidServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @param Dashboard $dashboard
     *
     * @return void
     */
    public function boot(Dashboard $dashboard): void
    {
        parent::boot($dashboard);

        // ...
    }

    /**
     * Register the application menu.
     *
     * @return Menu[]
     */
    public function menu(): array
    {
        return [
            Menu::make('Nauka Słówek')
                ->icon('eyeglasses')
                ->title('Norweski')
                ->route('platform.norwegian.words.learn'),

            Menu::make('Lista Słówek')
                ->icon('list')
                ->route('platform.norwegian.words.list'),

            Menu::make('Dodaj Słówka Do Bazy')
                ->icon('plus')
                ->route('platform.norwegian.words.add'),

            Menu::make('Nauka Czasowników')
                ->icon('eyeglasses')
                ->title('Angielski')
                ->route('platform.english.verbs.learn'),

            Menu::make('Lista Czasowników')
                ->icon('list')
                ->route('platform.english.verbs.list'),

            Menu::make('Dodaj Czasownik Do Bazy')
                ->icon('plus')
                ->route('platform.english.verbs.add'),

            Menu::make(__('Użytkownicy'))
                ->icon('bs.people')
                ->route('platform.systems.users')
                ->permission('platform.systems.users')
                ->title(__('Ustawienia')),

        ];
    }

    /**
     * Register permissions for the application.
     *
     * @return ItemPermission[]
     */
    public function permissions(): array
    {
        return [
            ItemPermission::group(__('System'))
                ->addPermission('platform.systems.roles', __('Roles'))
                ->addPermission('platform.systems.users', __('Users')),
        ];
    }
}
