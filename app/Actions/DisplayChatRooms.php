<?php

namespace App\Actions;

use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsAction;
use NunoMaduro\LaravelConsoleMenu\Menu;
use React\Socket\ConnectionInterface;

class DisplayChatRooms
{
    use AsAction;

    public function handle(ConnectionInterface $connection, Collection $rooms): void
    {
        with(new Menu('Select a chat room'), function (Menu $menu) use ($rooms, $connection) {
            $rooms->filter(fn ($room) => $room['people'] > 1)
                ->each(function ($room) use ($menu) {
                    $menu->addOption($room['name'], str($room['name'])->padRight(20, ' ')."({$room['people']})");
                });

            with($menu->disableDefaultItems()->open(), function (string $name) use ($connection) {
                LaunchChat::run($connection, $name);
            });
        });
    }
}
