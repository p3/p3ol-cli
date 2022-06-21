<?php

namespace App\Actions;

use Clue\React\Stdio\Stdio;
use Codedungeon\PHPCliColors\Color;
use Lorisleiva\Actions\Concerns\AsAction;

class SetChatHandle
{
    use AsAction;

    public function handle(Stdio $console, ?string $handle): void
    {
        if (! $handle) {
            return;
        }

        cache(['chat_handle' => $handle]);

        $console->write(Color::BG_BLUE.'Your handle has been set to: '.$handle.Color::RESET.PHP_EOL);
    }
}
