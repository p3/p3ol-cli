<?php

namespace App\Actions;

use AsciiTable\Builder;
use Clue\React\Stdio\Stdio;
use Lorisleiva\Actions\Concerns\AsAction;

class DisplayPeopleInChat
{
    use AsAction;

    public function handle(Stdio $console): void
    {
        if (! cache('room_list')) {
            return;
        }

        with(new Builder, function (Builder $builder) use ($console) {
            $builder->setTitle('People in Room:');
            $builder->addRows(
                cache('room_list')->map(fn ($screenName) => ['Screen Name' => $screenName])->toArray()
            );

            $console->write($builder->renderTable().PHP_EOL);
        });
    }
}
