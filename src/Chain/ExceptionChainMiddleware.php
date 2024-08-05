<?php

declare(strict_types=1);

namespace PhPhD\ExceptionHandler\Chain;

use LogicException;
use PhPhD\ExceptionHandler\Chain\Escalator\RaiseAsExceptionEscalator;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Stamp\BusNameStamp;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Throwable;

/**
 * @api
 *
 * @internal
 */
final class ExceptionChainMiddleware implements MiddlewareInterface
{
    private readonly RaiseAsExceptionEscalator $escalator;

    public function __construct()
    {
        $this->escalator = new RaiseAsExceptionEscalator();
    }

    /** @throws Throwable */
    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        /** @var Throwable $exception */
        $exception = $envelope->getMessage();
        $busName = $this->getBusName($envelope);

        $resultEnvelope = $stack->next()->handle($envelope, $stack);
        $handledStamp = $resultEnvelope->last(HandledStamp::class);

        if (null === $handledStamp) {
            $this->escalator->raiseException($exception, $busName);
        }

        return $resultEnvelope;
    }

    private function getBusName(Envelope $envelope): string
    {
        $busNameStamp = $envelope->last(BusNameStamp::class);

        if (null === $busNameStamp) {
            throw new LogicException('Bus name is required');
        }

        return $busNameStamp->getBusName();
    }
}
