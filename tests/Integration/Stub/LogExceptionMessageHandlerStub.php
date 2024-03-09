<?php

declare(strict_types=1);

namespace PhPhD\ExceptionHandlerBundle\Tests\Integration\Stub;

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
    private array $loggedExceptions = [];

    /** @throws Throwable */
    public function __invoke(Throwable $exception, object $command): void
    {
        $this->loggedExceptions[] = [$exception, $command];
    }

    public function getLoggedExceptions(): array
    {
        return $this->loggedExceptions;
    }
}
