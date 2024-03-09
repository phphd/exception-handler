<?php

declare(strict_types=1);

namespace PhPhD\ExceptionHandler\Result;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;

use function array_filter;

/**
 * @api
 *
 * @internal
 */
final class ExceptionHandlingResultFilterMiddleware implements MiddlewareInterface
{
    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        $resultEnvelope = $stack->next()->handle($envelope, $stack);

        /** @var HandledStamp[] $handledStamps */
        $handledStamps = $resultEnvelope->all(HandledStamp::class);

        $resultStamps = array_filter($handledStamps, static fn (HandledStamp $stamp): bool => null !== $stamp->getResult());

        return $resultEnvelope
            ->withoutAll(HandledStamp::class)
            ->with(...$resultStamps)
        ;
    }
}
