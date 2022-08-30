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

        $this->createMessageSession();

        with(new Builder(), function (Builder $builder) {
            $builder->setTitle('New Instant Message 💌');
            $builder->addRow([
                'Screenname' => $this->from(),
                'Message' => $this->message(),
            ]);

            $this->console->write($builder->renderTable().PHP_EOL);
            $this->console->write(Color::BG_BLUE.'Press the down arrow to reply'.Color::RESET.PHP_EOL);
            PlaySound::run('im');
        });
    }

    private function createMessageSession(): void
    {
        with(cache('instant_messages', collect()), function (Collection $sessions) {
            if (! $sessions->firstWhere('screenName', $this->from())) {
                cache(['instant_messages' => $sessions->push([
                    'globalId' => $this->globalId(),
                    'responseId' => $sessions->count(),
                    'screenName' => $this->from(),
                ])]);
            }
        });
    }

    private function from(): string
    {
        if ($screenName = $this->packet->atoms()->firstWhere('name', 'man_replace_data')?->toBinary()) {
            return $screenName;
        }

        return cache('instant_messages', collect())->firstWhere(['responseId' => $this->responseId()])['screenName'];
    }

    private function message(): string
    {
        return $this->packet->atoms()->firstWhere('name', 'man_append_data')->toBinary();
    }

    private function globalId(): ?string
    {
        return once(function () {
            return $this->packet->atoms()->last(function (Atom $atom) {
                return $atom->name === 'man_set_context_globalid';
            })?->data;
        });
    }

    private function responseId(): ?string
    {
        return once(function () {
            return $this->packet->atoms()->last(function (Atom $atom) {
                return $atom->name === 'man_set_context_response_id';
            })?->data;
        });
    }
}
