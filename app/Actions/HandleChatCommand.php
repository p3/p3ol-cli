<?php

namespace App\Actions;

use App\Events\QuitChat;
use Clue\React\Stdio\Stdio;
use Illuminate\Support\Facades\Cache;
use Lorisleiva\Actions\Concerns\AsAction;
use function Termwind\{render}; //@codingStandardsIgnoreLine

class HandleChatCommand
{
    use AsAction;

    public function handle(Stdio $console, string $command): void
    {
        match ($command) {
            '/quit' => QuitChat::dispatch($console),
            '/here' => $this->displayRoomList(),
            default =>  $console->write('We could not find a command for that.'.PHP_EOL)
        };
    }

    private function displayRoomList(): void
    {
        $roomList = Cache::get('room_list');

        $screenNames = $roomList->map(fn ($screenName) => "<tr><td>{$screenName}</td></tr>")->join('');

        render("\n");
        render(<<<HTML
            <table>
                <thead>
                    <tr>
                        <th>Screenname</th>
                    </tr>
                </thead>
                {$screenNames}
            </table>
        HTML);
    }
}
