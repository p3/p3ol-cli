<?php

namespace App\Actions;

use Lorisleiva\Actions\Concerns\AsAction;
use function Termwind\{render}; //@codingStandardsIgnoreLine

class DisplayWelcome
{
    use AsAction;

    public function handle(): void
    {
        render("\e[H\e[J");

        //@codingStandardsIgnoreStart
        render(<<<'HTML'
            <div class="px-1 text-blue-200">
                &nbsp;____&nbsp;____&nbsp;_________&nbsp;____&nbsp;____&nbsp;____&nbsp;____&nbsp;____&nbsp;____&nbsp;____&nbsp;<br>
                ||<span class="text-white">{</span>&nbsp;|||<span class="text-white">s</span>&nbsp;|||&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;|||<span class="text-white">w</span>&nbsp;|||<span class="text-white">e</span>&nbsp;|||<span class="text-white">l</span>&nbsp;|||<span class="text-white">c</span>&nbsp;|||<span class="text-white">o</span>&nbsp;|||<span class="text-white">m</span>&nbsp;|||<span class="text-white">e</span>&nbsp;||<br>
                ||__|||__|||_______|||__|||__|||__|||__|||__|||__|||__||<br>
                |/__\|/__\|/_______\|/__\|/__\|/__\|/__\|/__\|/__\|/__\|<br>
            </div>
        HTML);
        //@codingStandardsIgnoreEnd
    }
}
