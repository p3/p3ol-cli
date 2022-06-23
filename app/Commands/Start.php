<?php

namespace App\Commands;

use App\Actions\DisplayLogonMenu;
use App\Actions\DisplayWelcome;
use App\Actions\FetchChatRooms;
use App\Actions\LoginAsGuest;
use App\Actions\Logoff;
use App\Actions\StartHeartbeat;
use App\Events\QuitChat;
use App\Events\StopHeartbeat;
use App\Events\SuccessfulLogin;
use App\Helpers\Packet;
use Illuminate\Support\Facades\Event;
use LaravelZero\Framework\Commands\Command;
use React\EventLoop\Loop;
use React\Socket\ConnectionInterface;
use React\Socket\Connector;
use Symfony\Component\Console\Command\SignalableCommandInterface;

class Start extends Command implements SignalableCommandInterface
{
    public const HOST = 'americaonline.reaol.org:5190';

    protected ConnectionInterface $connection;

    protected $signature = 'start';

    protected $description = 'Start the RE-AOL CLI client';

    public function handle()
    {
        DisplayLogonMenu::run();

        $this->registerEventListeners();

        $this->connect();

        Loop::run();
    }

    public function connect()
    {
        with(new Connector(), function (Connector $connector) {
            $connector->connect(self::HOST)->then(function (ConnectionInterface $connection) {
                $this->connection = $connection;
                cache(['start_time' => microtime(true)]);

                $connection->on('data', function ($data) {
                    with(Packet::make($data), function (Packet $packet) {
                        if (! \Phar::running()) {
                            info($packet->toHex());
                        }

                        $packet->incrementSequence();
                    });
                });

                $connection->on('close', function () {
                    StopHeartbeat::dispatch();
                });

                LoginAsGuest::run($connection);
                StartHeartbeat::run($connection);
            });
        });
    }

    private function registerEventListeners()
    {
        Event::listen(SuccessfulLogin::class, function () {
            DisplayWelcome::run();
            FetchChatRooms::run($this->connection);
        });

        Event::listen(QuitChat::class, function (QuitChat $event) {
            $this->handleSignal(15);
        });
    }

    public function getSubscribedSignals(): array
    {
        return [SIGINT, SIGTERM];
    }

    public function handleSignal(int $signal): void
    {
        if (isset($this->connection)) {
            Logoff::run($this->connection);
        }
    }
}
