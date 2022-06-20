<?php

namespace App\Traits;

use App\Actions\PlaySound;

trait Sound
{
    protected array $sounds = [
        'buddyin',
        'buddyout',
        'drop',
        'filedone',
        'goodbye',
        'gotmail',
        'im',
        'welcome',
    ];

    public function playSoundFromText(string $text): void
    {
        with(implode('|', $this->sounds), function ($pattern) use ($text) {
            if (preg_match("/{s\s($pattern)(\s|$)+/i", $text, $matches)) {
                PlaySound::run($matches[1]);
            }
        });
    }
}
