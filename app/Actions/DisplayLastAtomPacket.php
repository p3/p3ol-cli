<?php

namespace App\Actions;

use Clue\React\Stdio\Stdio;
use Codedungeon\PHPCliColors\Color;
use Lorisleiva\Actions\Concerns\AsAction;

class DisplayLastAtomPacket
{
    use AsAction;

    public function handle(Stdio $console): void
    {
        $output = str(cache('last_atom_packet'))
            ->replaceMatches('/<(.*?)>/', function ($match) {
                return '<'.Color::bold_green().$match[1].Color::RESET.'>';
            })
            ->replaceMatches('/uni_(.*?)\s/', function ($match) {
                return Color::BLUE.$match[0].Color::RESET;
            })
            ->replaceMatches('/man_(.*?)\s/', function ($match) {
                return Color::RED.$match[0].Color::RESET;
            })
            ->replaceMatches('/mat_(.*?)\s/', function ($match) {
                return Color::YELLOW.$match[0].Color::RESET;
            })
            ->replaceMatches('/chat_(.*?)\s/', function ($match) {
                return Color::CYAN.$match[0].Color::RESET;
            })
            ->replaceMatches('/act_(.*?)\s/', function ($match) {
                return Color::WHITE.$match[0].Color::RESET;
            })
            ->replaceMatches('/if_(.*?)\s/', function ($match) {
                return Color::GRAY.$match[0].Color::RESET;
            })
            ->replaceMatches('/""".*?"""(*SKIP)(*FAIL)|(,)/', function ($match) {
                return Color::WHITE.','.Color::bold_green();
            });

        $console->write($output.PHP_EOL);
    }
}
