<?php

namespace App\Actions;

use App\Actions\DisplayChatRooms;
use App\DTO\Packet;
use App\Enums\ChatroomPacket;
use App\Traits\RemoveListener;
use Illuminate\Console\OutputStyle;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;
use Rahul900Day\LaravelConsoleSpinner\Spinner;
use React\EventLoop\Loop;
use React\EventLoop\TimerInterface;
use React\Socket\ConnectionInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;

class FetchChatRooms
{
    use AsAction;
    use RemoveListener;
    use WithAttributes;

    protected Spinner $spinner;

    protected TimerInterface $spinnerTimer;

    public function handle(ConnectionInterface $connection): void
    {
        $this->initializeSpinner();

        $this->set('packets', collect());
        $this->set('connection', $connection);

        $connection->write(hex2binary(ChatroomPacket::CJ_PACKET->value));

        $connection->on('data', function (string $data) {
            with(Packet::make($data), function (Packet $packet) {
                if ($packet->token() === 'AT') {
                    $this->packets->push($packet);
                    $this->startTimer();
                }
            });
        });
    }

    private function parseChatrooms(): Collection
    {
        return $this->packets
            ->filter(fn (Packet $packet) => str_contains($packet->hex(), '0001000109032000620f13020102010a010101'))
            ->map(fn (Packet $packet) => substr($packet->hex(), 66))
            ->flatMap(function ($hex) {
                preg_match_all('/06(\d{2,4})09(.*?)100b/', $hex, $output);

                return collect($output[1])->zip($output[2])->toArray();
            })
            ->map(fn ($value) => ['people' => intval(hex2binary($value[0])), 'name' => hex2binary($value[1])]);
    }

    private function startTimer(): void
    {
        once(function () {
            Loop::addTimer(5, function () {
                Loop::cancelTimer($this->spinnerTimer);
                $this->removeListener('data', $this->connection);

                DisplayChatRooms::run($this->connection, $this->parseChatrooms());
            });
        });
    }

    private function initializeSpinner()
    {
        $output = new OutputStyle(new ArrayInput([]), new ConsoleOutput());
        $this->spinner = new Spinner($output, 1000);

        $this->spinner->setMessage(' Fetching chatrooms 💬 ');
        $this->spinner->start();

        $this->spinnerTimer = Loop::addPeriodicTimer(0.003, function () {
            $this->spinner->advance();
        });
    }
}
