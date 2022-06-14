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
    }
}
