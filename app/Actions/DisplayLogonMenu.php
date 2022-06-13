<?php

namespace App\Actions;

use Lorisleiva\Actions\Concerns\AsAction;
use NunoMaduro\LaravelConsoleMenu\Menu;
use function Termwind\{ask}; //@codingStandardsIgnoreLine
use function Termwind\{render}; //@codingStandardsIgnoreLine

class DisplayLogonMenu
{
    use AsAction;

    public function handle(): void
    {
        //@codingStandardsIgnoreStart
        render(<<<'HTML'
            <div class="py-1 ml-2">
                <div class="px-1 bg-blue-300 text-black">🖥 &nbsp;CLI Edition (Alpha)</div><br>
                <em class="mt-2 text-blue-200">
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;.o.&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;.oooooo.&nbsp;&nbsp;&nbsp;ooooo&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br>
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;.888.&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;d8P'&nbsp;&nbsp;`Y8b&nbsp;&nbsp;`888'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br>
                    oooo&nbsp;d8b&nbsp;&nbsp;.ooooo.&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;.8"888.&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;888&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;888&nbsp;&nbsp;888&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br>
                    `888""8P&nbsp;d88'&nbsp;`88b&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;.8'&nbsp;`888.&nbsp;&nbsp;&nbsp;&nbsp;888&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;888&nbsp;&nbsp;888&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br>
                    &nbsp;888&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;888ooo888&nbsp;8888888&nbsp;&nbsp;&nbsp;.88ooo8888.&nbsp;&nbsp;&nbsp;888&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;888&nbsp;&nbsp;888&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br>
                    &nbsp;888&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;888&nbsp;&nbsp;&nbsp;&nbsp;.o&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;.8'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;`888.&nbsp;&nbsp;`88b&nbsp;&nbsp;&nbsp;&nbsp;d88'&nbsp;&nbsp;888&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;o&nbsp;<br>
                    d888b&nbsp;&nbsp;&nbsp;&nbsp;`Y8bod8P'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;o88o&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;o8888o&nbsp;&nbsp;`Y8bood8P'&nbsp;&nbsp;o888ooooood8<br>
                </em>
                <div class="text-blue-500 mt-2">ascii by keeb</div>
            </div>
        HTML);
        //@codingStandardsIgnoreEnd

        ask('<div class="px-1 bg-blue-300 text-black">Press enter to continue.</div>');

        (new Menu('Sign on', ['Guest']))->disableDefaultItems()->open();
    }
}
