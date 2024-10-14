<?php

declare(strict_types=1);

namespace PhPhD\ExceptionHandler\Middleware\Rethrow;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Throwable;

/**
 * @api
 *
 * @internal
 */
final class RethrowUnhandledExceptionMiddleware implements MiddlewareInterface
{
    /** @throws Throwable */
    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        /** @var Throwable $exception */
        $exception = $envelope->getMessage();

        $resultEnvelope = $stack->next()->handle($envelope, $stack);

        $handledStamp = $resultEnvelope->last(HandledStamp::class);

        if (null === $handledStamp) {
            throw $exception;
        }

        return $resultEnvelope;
    }
}
