<?php

namespace App\Actions;

use Lorisleiva\Actions\Concerns\AsAction;
use NunoMaduro\LaravelDesktopNotifier\Facades\Notifier;
use NunoMaduro\LaravelDesktopNotifier\Notification;

class SendDesktopNotification
{
    use AsAction;

    public function handle(string $title, string $message): void
    {
        with((new Notification())->setTitle($title)->setBody($message), function ($notification) {
            Notifier::send($notification);
        });
    }
}
