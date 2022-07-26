<?php

namespace App\Actions;

use App\ValueObjects\Atom;
use App\ValueObjects\Packet;
use AsciiTable\Builder;
use Clue\React\Stdio\Stdio;
use Codedungeon\PHPCliColors\Color;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class HandleInstantMessagePacket
{
    use AsAction, WithAttributes;

    public function handle(Stdio $console, Packet $packet): void
    {
        $this->set('console', $console);
        $this->set('packet', $packet);

        match (true) {
            cache('instant_messages', collect())->has($this->globalId()) => $this->parseFromPrevious(),
            default => $this->parseFromNew()
        };
    }

    private function parseFromPrevious(): void
    {
        $this->handleInstantMessage(
            cache('instant_messages')[$this->globalId()],
            $this->packet->atoms()->firstWhere('name', 'man_append_data')->toBinary()
        );
    }

    private function parseFromNew(): void
    {
        with(cache('instant_messages', collect()), function (Collection $instantMessages): void {
            cache(['instant_messages' => $instantMessages->put(
                $this->globalId(),
                $this->packet->atoms()->firstWhere('name', 'man_replace_data')->toBinary(),
            )]);
        });

        $this->handleInstantMessage(
            $this->packet->atoms()->firstWhere('name', 'man_replace_data')->toBinary(),
            $this->packet->atoms()->firstWhere('name', 'man_append_data')->toBinary()
        );
    }

    private function handleInstantMessage($screenName, $message): void
    {
        with(new Builder(), function (Builder $builder) use ($screenName, $message) {
            $builder->setTitle('New Instant Message 💌');
            $builder->addRow([
                'Screenname' => $screenName,
                'Message' =>  $message,
            ]);

            $this->console->write($builder->renderTable().PHP_EOL);
            $this->console->write(Color::BG_BLUE.'Press the down arrow to reply'.Color::RESET.PHP_EOL);
            PlaySound::run('im');
        });
    }

    private function globalId(): ?string
    {
        return once(function () {
            return $this->packet->atoms()->last(function (Atom $atom) {
                return $atom->name === 'man_set_context_globalid';
            })?->data;
        });
    }
}
