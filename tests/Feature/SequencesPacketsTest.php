<?php

use App\Actions\SendChatMessage;
use function Clue\React\Block\sleep;
use React\Socket\ConnectionInterface;

it('can sequence the tx byte of packets', function () {
    $sequences = collect();

    $this->client->connect(function (ConnectionInterface $connection) use ($sequences) {
        foreach (range(0, 112) as $i) {
            SendChatMessage::run($connection, 'hey abc what is up my friend');
            $sequences->push([
                'index' => $i + 15,
                'txSequence' => cache('tx_sequence', 127),
                'lastPacket' => isset($this->server->packet) ? $this->server->packet->toHex() : null,
            ]);
            sleep(0.01);
        }
    });

    sleep(.1);

    $sequences->each(function ($result) {
        match ($result['index']) {
            15 => expect($result['txSequence'])->toBe(127),
            default => expect($result['index'])->toBe($result['txSequence'])
        };

        match ($result['index']) {
            15 => null,
            16 => expect(substr($result['lastPacket'], 12, 2))->toBe('7f'),
            default => expect(substr($result['lastPacket'], 12, 2))->toBe(dechex($result['txSequence'] - 1))
        };
    });
});
