<?php

namespace App\Actions;

use App\Enums\ChatPacket;
use App\Traits\RemoveListener;
use App\ValueObjects\Atom;
use App\ValueObjects\Packet;
use Illuminate\Console\OutputStyle;
use Illuminate\Support\Collection;
use Illuminate\Support\Stringable;
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
        $this->set('packets', collect());
        $this->set('connection', $connection);
        $this->set('console', new ConsoleOutput());

        $this->initializeSpinner();

        $connection->write(Packet::make(ChatPacket::CJ_PACKET->value)->prepare());

        $connection->on('data', function (string $data) {
            with(Packet::make($data), function (Packet $packet) {
                if ($packet->token() === 'AT') {
                    $this->packets->push($packet);
                    $this->startTimer();
                }
            });
        });
    }

    private function parseChatRooms(): Collection
    {
        return $this->packets
            ->map(fn (Packet $packet) => $packet->atoms())
            ->filter(function (Collection $atoms): bool {
                return $atoms->firstWhere('name', 'man_set_context_globalid')?->data === '32-98';
            })
            ->flatMap(fn (Collection $atoms) => $this->parseChatRoomsFromAtoms($atoms))
            ->unique();
    }

    private function parseChatRoomsFromAtoms(Collection $atoms): Collection
    {
        return $atoms
            ->where('name', 'man_start_object')
            ->map(function (Atom $atom) {
                return with(str($atom->hex)->substr(2), function (Stringable $hex) {
                    return [
                        'people' => intval(hex2binary($hex->before('09'))),
                        'name' => hex2binary($hex->after('09')),
                    ];
                });
            });
    }

    private function startTimer(): void
    {
        once(function () {
            Loop::addTimer(5, function () {
                Loop::cancelTimer($this->spinnerTimer);
                $this->removeListener('data', $this->connection);
                $this->console->write("\033[?25h");

                DisplayChatRooms::run($this->connection, $this->parseChatRooms());
            });
        });
    }

    private function initializeSpinner(): void
    {
        $this->spinner = new Spinner($this->outputStyle(), 1000);

        $this->console->write("\033[?25l");
        $this->spinner->setMessage('Fetching chatrooms 💬');
        $this->spinner->start();

        $this->spinnerTimer = Loop::addPeriodicTimer(0.003, function () {
            $this->spinner->advance();
        });
    }

    private function outputStyle(): OutputStyle
    {
        return resolve('Illuminate\Console\OutputStyle', [new ArrayInput([]), $this->console]);
    }
}
