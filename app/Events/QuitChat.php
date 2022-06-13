<?php

namespace App\Events;

use Clue\React\Stdio\Stdio;
use Illuminate\Foundation\Events\Dispatchable;

class QuitChat
{
    use Dispatchable;

    public function __construct(
        public Stdio $console
    ) {
    }
}
