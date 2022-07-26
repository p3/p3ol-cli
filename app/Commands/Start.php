<?php

namespace App\Commands;

use App\Actions\DisplayLogonMenu;
use App\Actions\DisplayWelcome;
use App\Actions\FetchChatRooms;
use App\Actions\IncreasePacketSequence;
use App\Actions\Login;
use App\Actions\Logoff;
use App\Actions\StartHeartbeat;
use App\Enums\SignOnState;
use App\Events\InvalidLogin;
use App\Events\QuitChat;
use App\Events\StopHeartbeat;
use App\Events\SuccessfulLogin;
use App\ValueObjects\AtomPacket;
use App\ValueObjects\Packet;
use Illuminate\Support\Facades\Event;
use LaravelZero\Framework\Commands\Command;
use React\EventLoop\Loop;
use React\Socket\ConnectionInterface;
use React\Socket\Connector;
use Symfony\Component\Console\Command\SignalableCommandInterface;

class Start extends Command implements SignalableCommandInterface
{
    public const HOST = 'americaonline.reaol.org:5190';
    // public const HOST = 'staging.re-aol.com:5190';

    protected ConnectionInterface $connection;

    protected $signature = 'start';

    protected $description = 'Start the RE-AOL CLI client';

    protected SignOnState $state = SignOnState::OFFLINE;

    protected array $credentials;

    public function handle()
    {
        $this->credentials = DisplayLogonMenu::run($this->state);

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
                            $this->writeDebugLog($packet);
                        }

                        if ($packet->token() === 'AT') {
                            cache(['last_atom_packet' => $packet->toFDO()]);
                        }

                        IncreasePacketSequence::run($packet);
                    });
                });

                $connection->on('close', function () {
                    if ($this->state === SignOnState::ONLINE) {
                        StopHeartbeat::dispatch();
                    }
                });

                Login::run($connection, $this->credentials);
                StartHeartbeat::run($connection);
            });
        });
    }

    private function registerEventListeners()
    {
        Event::listen(SuccessfulLogin::class, function () {
            $this->state = SignOnState::ONLINE;
            DisplayWelcome::run();
            FetchChatRooms::run($this->connection);
        });

        Event::listen(InvalidLogin::class, function () {
            $this->state = SignOnState::INVALID;

            $this->handle();
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

    private function writeDebugLog(Packet $packet): void
    {
        info('Token: '.$packet->token() ?? '[None]');
        info($packet->toHex());
        if ($packet instanceof AtomPacket) {
            info(PHP_EOL.$packet->toFDO());
        }
    }
}
