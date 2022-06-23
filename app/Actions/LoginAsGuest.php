<?php

namespace App\Actions;

use App\Enums\AuthPacket;
use App\Enums\SignOnState;
use App\Events\SuccessfulLogin;
use App\Helpers\Packet;
use App\Traits\RemoveListener;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;
use React\Socket\ConnectionInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutput;
use function Termwind\{render}; //@codingStandardsIgnoreLine

class LoginAsGuest
{
    use AsAction;
    use RemoveListener;
    use WithAttributes;

    protected ProgressBar $progressBar;

    protected SignOnState $state = SignOnState::OFFLINE;

    public function handle(ConnectionInterface $connection): void
    {
        $this->set('connection', $connection);

        $this->initializeProgressBar();
        $this->sendVersionPacket();

        $connection->on('data', fn (string $data) => $this->processPacket(Packet::make($data)));
    }

    private function processPacket(Packet $packet): void
    {
        match (true) {
            $this->needsDdPacket($packet) => $this->sendDdPacket(),
            $this->needsScPacket($packet) => $this->sendScPacket(),
            $this->confirmAuth($packet) => $this->successfulLogin(),
            default => info($packet->toHex())
        };
    }

    private function needsDdPacket(Packet $packet): bool
    {
        return $this->state === SignOnState::NEEDS_Dd_PACKET && $packet->toHex() === '5ab71100037f7f240d';
    }

    private function sendDdPacket(): void
    {
        $this->connection->write(Packet::make(AuthPacket::Dd_PACKET->value)->prepare());

        $this->updateProgressBar('Step 2: Shaking hands ...', 50);

        $this->state = SignOnState::NEEDS_SC_PACKET;
    }

    private function needsScPacket(Packet $packet): bool
    {
        return $this->state === SignOnState::NEEDS_SC_PACKET && str_contains($packet->toHex(), '5343');
    }

    private function sendScPacket(): void
    {
        $this->connection->write(Packet::make((AuthPacket::SC_PACKET->value))->prepare());

        $this->updateProgressBar('Step 3: Wrapping up ...', 75);

        $this->state = SignOnState::AWAITING_WELCOME;
    }

    private function confirmAuth(Packet $packet): bool
    {
        return $this->state === SignOnState::AWAITING_WELCOME && str_contains($packet->data, 'Welcome');
    }

    private function successfulLogin(): void
    {
        $this->state = SignOnState::ONLINE;

        $this->progressBar->finish();

        $this->removeListener('data', $this->connection);

        PlaySound::run('welcome');

        SuccessfulLogin::dispatch();
    }

    protected function sendVersionPacket(): void
    {
        $this->connection->write(hex2binary(AuthPacket::VERSION->value));

        $this->state = SignOnState::NEEDS_Dd_PACKET;
    }

    private function initializeProgressBar(): void
    {
        render('<div class="px-1 bg-blue-300 text-black">🖥 &nbsp;RE-AOL CLI Edition (Alpha)</div>');

        $this->progressBar = new ProgressBar(new ConsoleOutput(), 100);

        if ('\\' !== \DIRECTORY_SEPARATOR || 'Hyper' === getenv('TERM_PROGRAM')) {
            $this->progressBar->setEmptyBarCharacter('░');
            $this->progressBar->setProgressCharacter('');
            $this->progressBar->setBarCharacter('▓');
        }

        $this->progressBar->setFormat("%message%\n [%bar%] %percent:3s%%");
        $this->progressBar->setMessage('Step 1: Initializing TCP/IP ...');
        $this->progressBar->setProgress(25);
    }

    private function updateProgressBar(string $message, int $value): void
    {
        $this->progressBar->setMessage($message);
        $this->progressBar->setProgress($value);
    }
}
