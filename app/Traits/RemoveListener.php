<?php

namespace App\Traits;

use Closure;
use React\Socket\ConnectionInterface;
use ReflectionFunction;

trait RemoveListener
{
    public function removeListener(string $type, ConnectionInterface $connection): void
    {
        $connection->removeListener($type, $this->findClosure($connection, get_called_class()));
    }

    private function findClosure(ConnectionInterface $connection, string $className): Closure
    {
        return collect($connection->listeners('data'))->first(function ($closure) use ($className) {
            return (new ReflectionFunction($closure))->getClosureScopeClass()->getName() === $className;
        });
    }
}
