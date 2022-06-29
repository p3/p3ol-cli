<?php

namespace App\Actions;

use Illuminate\Support\Facades\File;
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

        if (env('APP_ENV') === 'testing') {
            return;
        }

        if (microtime(true) - cache('sound_last_played') < 2) {
            return;
        }

        match (PHP_OS_FAMILY) {
            'Darwin' => $this->playSoundWith('afplay'),
            'Linux' => $this->playSoundWith('aplay'),
            default => null
        };
    }

    private function playSoundWith(string $player): void
    {
        cache(['sound_last_played' => microtime(true)]);

        if (! File::isDirectory(getcwd().'/.reaol')) {
            File::makeDirectory(getcwd().'/.reaol');
        }

        if (! File::exists(getcwd().'/.reaol/'.$this->fileName.'.wav')) {
            File::copy(resource_path().'/'.$this->fileName.'.wav', getcwd().'/.reaol/'.$this->fileName.'.wav');
        }

        (new Process([$player, getcwd().'/.reaol/'.$this->fileName.'.wav']))->run();
    }
}
