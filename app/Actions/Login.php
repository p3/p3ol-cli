<?php

namespace App\Actions;

use App\Enums\AuthPacket;
use App\Enums\SignOnState;
use App\Events\InvalidLogin;
use App\Events\SuccessfulLogin;
use App\Traits\RemoveListener;
use App\ValueObjects\Packet;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;
use React\Socket\ConnectionInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;
use function Termwind\{renderUsing}; //@codingStandardsIgnoreLine
use function Termwind\{render}; //@codingStandardsIgnoreLine

class Login
{
    use AsAction;
    use RemoveListener;
    use WithAttributes;

    protected ProgressBar $progressBar;

    protected SignOnState $state = SignOnState::OFFLINE;

    public function handle(ConnectionInterface $connection, array $credentials): void
    {
        $this->set('connection', $connection);
        $this->set('credentials', $credentials);

        $this->initializeProgressBar();
        $this->sendVersionPacket();

        $connection->on('data', fn (string $data) => $this->processPacket(Packet::make($data)));
    }

    private function processPacket(Packet $packet): void
    {
        match (true) {
            $this->needsDdPacket($packet) => $this->sendDdPacket(),
            $this->needsScPacket($packet) => $this->sendScPacket(),
            $this->hasInvalidLogin($packet) => $this->handleInvalidLogin(),
            $this->hasSuccessfulLogin($packet) => $this->handleSuccessfulLogin(),
            $this->needsUdPAcket($packet) => $this->sendUdPacket(),
            default => info($packet->toHex())
        };
    }

    private function needsDdPacket(Packet $packet): bool
    {
        return $this->state === SignOnState::NEEDS_Dd && $packet->takeNumber(1)->toHex() === '5ab71100037f7f240d';
    }

    private function sendDdPacket(): void
    {
        with([$screenName, $password] = $this->credentials, function () use ($screenName, $password) {
            match ($screenName) {
                'guest' => $this->sendGuestDdPacket(),
                default => $this->sendAuthDdPacket($screenName, $password),
            };
        });

        $this->updateProgressBar('Step 2: Shaking hands ...', 50);

        $this->state = SignOnState::NEEDS_SC;
    }

    private function sendGuestDdPacket(): void
    {
        $this->connection->write(Packet::make(AuthPacket::Dd_GUEST_PACKET->value)->prepare());
    }

    private function sendAuthDdPacket(string $screenName, string $password): void
    {
        with(AuthPacket::Dd_AUTH_PACKET->value, function ($packet) use ($screenName, $password) {
            $packet = str_replace('{screenName}', bin2hex($screenName), $packet);
            $packet = str_replace('{password}', bin2hex($password), $packet);
            $packet = substr_replace($packet, calculatePacketLengthByte($packet), 8, 2);

            $this->connection->write(Packet::make($packet)->prepare());
        });
    }

    private function needsScPacket(Packet $packet): bool
    {
        return $this->state === SignOnState::NEEDS_SC && str_contains($packet->toHex(), '5343');
    }

    private function sendScPacket(): void
    {
        $this->connection->write(Packet::make((AuthPacket::SC_PACKET->value))->prepare());

        $this->updateProgressBar('Step 3: Wrapping up ...', 75);

        $this->state = SignOnState::AWAITING_WELCOME;
    }

    private function needsUdPAcket(Packet $packet): bool
    {
        return $packet->token()?->name === 'AT' && str_contains($packet->toHex(), '7544');
    }

    private function sendUdPacket(): void
    {
        with(AuthPacket::uD_PACKET->value, function ($packet) {
            $packet = str_replace('{timestamp}', bin2hex(time()), $packet);
            $packet = substr_replace($packet, calculatePacketLengthByte($packet), 8, 2);

            $this->connection->write(Packet::make($packet)->prepare());
        });
    }

    private function hasSuccessfulLogin(Packet $packet): bool
    {
        return $this->state === SignOnState::AWAITING_WELCOME && str_contains($packet->data, 'Welcome');
    }

    private function hasInvalidLogin(Packet $packet): bool
    {
        return $this->state === SignOnState::NEEDS_SC
            && (str_contains($packet->data, 'incorrect!') || str_contains($packet->data, 'login-000002'));
    }

    private function handleSuccessfulLogin(): void
    {
        $this->state = SignOnState::ONLINE;

        $this->progressBar->finish();

        $this->removeListener('data', $this->connection);

        PlaySound::run('welcome');

        SuccessfulLogin::dispatch();
    }

    private function handleInvalidLogin(): void
    {
        $this->removeListener('data', $this->connection);

        InvalidLogin::dispatch();
    }

    protected function sendVersionPacket(): void
    {
        $this->connection->write(hex2binary(AuthPacket::VERSION_PACKET->value));

        $this->state = SignOnState::NEEDS_Dd;
    }

    private function initializeProgressBar(): void
    {
        renderUsing($this->output());
        render('<div class="px-1 bg-blue-300 text-black">🖥 &nbsp;RE-AOL CLI Edition (Alpha)</div>');

        $this->progressBar = new ProgressBar($this->output(), 100);

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

    private function output(): OutputInterface
    {
        return resolve('Symfony\Component\Console\Output\ConsoleOutput');
    }
}
