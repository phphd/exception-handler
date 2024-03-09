<?php

declare(strict_types=1);

namespace PhPhD\ExceptionHandlerBundle\Tests\Integration\Stub;

use stdClass;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Throwable;

/**
 * @api
 *
 * @internal
 */
#[AutoconfigureTag('messenger.message_handler', [
    'handles' => stdClass::class,
    'bus' => 'command.bus',
])]
#[AutoconfigureTag('messenger.message_handler', [
    'handles' => stdClass::class,
    'bus' => 'query.bus',
])]
final class MessageHandlerStub
{
    /**
     * @psalm-suppress UnusedParam
     *
     * @throws Throwable
     */
    public function __invoke(object $message, Throwable $exceptionToThrow): never
    {
        throw $exceptionToThrow;
    }
}
