<?php

namespace App\Actions;

use AsciiTable\Builder;
use Clue\React\Stdio\Stdio;
use Codedungeon\PHPCliColors\Color;
use Lorisleiva\Actions\Concerns\AsAction;

class DisplayPeopleInChat
{
    use AsAction;

    public function handle(Stdio $console): void
    {
        if (! cache('room_list')) {
            return;
        }

        if (cache('room_list')->count() === 0) {
            $console->write(Color::BG_BLUE.'There are currently no other users here.'.Color::RESET.PHP_EOL);

            return;
        }

        with(new Builder(), function (Builder $builder) use ($console) {
            $builder->setTitle('People in Room:');
            $builder->addRows(
                cache('room_list')->map(fn ($screenName) => ['Screen Name' => $screenName])->toArray()
            );

            $console->write($builder->renderTable().PHP_EOL);
        });
    }
}
