<?php

declare(strict_types=1);

namespace PhPhD\ExceptionHandler\Bundle\Tests\Stub;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Throwable;

#[AutoconfigureTag('messenger.message_handler', [
    'handles' => Throwable::class,
    'bus' => 'query.exception.bus',
])]
#[AutoconfigureTag('messenger.message_handler', [
    'handles' => Throwable::class,
    'bus' => 'api.command.exception.bus',
])]
final class LogExceptionMessageHandlerStub
{
    /** @var array<array{Throwable,object}> */
    private array $loggedExceptions = [];

    /** @throws Throwable */
    public function __invoke(Throwable $exception, object $command): void
    {
        $this->loggedExceptions[] = [$exception, $command];
    }

    /** @return array<array{Throwable,object}> */
    public function getLoggedExceptions(): array
    {
        return $this->loggedExceptions;
    }
}
