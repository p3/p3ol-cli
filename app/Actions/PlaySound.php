<?php

namespace App\Actions;

use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;
use Symfony\Component\Process\Process;

class PlaySound
{
    use AsAction;
    use WithAttributes;

    public function handle(string $fileName): void
    {
        $this->set('fileName', $fileName);

        match (PHP_OS_FAMILY) {
            'Darwin' => $this->playSoundWith('afplay'),
            'Linux' => $this->playSoundWith('aplay'),
            default => null
        };
    }

    private function playSoundWith(string $player): void
    {
        (new Process([$player, './resources/'.$this->fileName.'.wav']))->run();
    }
}
