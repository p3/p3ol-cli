<?php

namespace App\Actions;

use App\Enums\ClientPacket;
use App\Helpers\Packet;
use App\Traits\RemoveListener;
use AsciiTable\Builder;
use Clue\React\Stdio\Stdio;
use Codedungeon\PHPCliColors\Color;
use Illuminate\Support\Collection;
use Illuminate\Support\Stringable;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;
use React\Socket\ConnectionInterface;

class FetchProfile
{
    use AsAction;
    use RemoveListener;
    use WithAttributes;

    protected array $headers = [
        'Member Name',
        'Age',
        'Sex',
        'Marital Status',
        'Occupation',
        'Interests',
        'Bio',
    ];

    public function handle(Stdio $console, ConnectionInterface $connection, ?string $screenName): void
    {
        $this->set('console', $console);
        $this->set('connection', $connection);
        $this->set('screenName', $screenName);

        if (! $screenName) {
            return;
        }

        with(ClientPacket::ji_PACKET->value, function ($packet) {
            $screenNameLengthByte = str_pad(dechex(strlen($this->screenName)), 2, '0', STR_PAD_LEFT);
            $packet = str_replace('{screenName}', $screenNameLengthByte.bin2hex($this->screenName), $packet);
            $packet = substr_replace($packet, calculatePacketLengthByte($packet), 8, 2);

            $this->connection->write(Packet::make($packet)->prepare());
        });

        $connection->on('data', function (string $data) {
            with(Packet::make($data), function (Packet $packet) {
                if ($packet->token() === 'AT') {
                    $this->displayProfile($packet);
                    $this->removeListener('data', $this->connection);
                }
            });
        });
    }

    private function displayProfile(Packet $packet): void
    {
        if (str_contains($packet->toBinary(), 'This user does not currently have a profile')) {
            $this->console->write(Color::BG_BLUE.'This user does not have a profile'.Color::RESET.PHP_EOL);

            return;
        }

        with(new Builder(), function (Builder $builder) use ($packet) {
            $this->console->write(PHP_EOL);

            $builder->setTitle(Color::BG_BLUE.'Member Directory Profile'.Color::RESET);

            with($this->parseProfile($packet), function ($profile) use ($builder) {
                $rows = $profile->map(fn ($value, $key) => [$this->screenName => $key, '' => $value])->values();

                $builder->addRows($rows->toArray());
            });

            $this->console->write($builder->renderTable().PHP_EOL);
        });
    }

    private function parseProfile(Packet $packet): Collection
    {
        return $this->parsePacket($packet)
            ->explode('7f')
            ->filter()
            ->slice(0, -1)
            ->map(fn ($hex) => [, $value] = parseArguments($hex, 2, '3a09'))
            ->mapWithKeys(fn ($entry, $key) => [$this->headers[$key] => $this->cleanse($entry[1])])
            ->merge($this->parseBio($packet));
    }

    private function parseBio(Packet $packet): array
    {
        if (! $this->parsePacket($packet)->contains('7f7f')) {
            return ['Bio' => '[None]'];
        }

        return with($this->parsePacket($packet)->after('7f7f')->before('011100011d0001'), function ($bio) {
            return['Bio' => preg_replace('/[[:^print:]]/', '', hex2binary($bio))];
        });
    }

    private function parsePacket(Packet $packet): Stringable
    {
        return once(fn () => $packet->split()->slice(2, 1)->pipe(fn ($hex) => str($hex->first())));
    }

    private function cleanse(string $value): string
    {
        return preg_replace('/[^A-z ]/', '', trim(hex2binary($value))) ?: '[None]';
    }
}
