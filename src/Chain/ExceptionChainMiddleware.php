<?php

declare(strict_types=1);

namespace PhPhD\ExceptionHandler\Chain;

use LogicException;
use PhPhD\ExceptionHandler\Chain\Elevator\RaiseAsExceptionElevator;
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
    private readonly RaiseAsExceptionElevator $elevator;

    public function __construct()
    {
        $this->elevator = new RaiseAsExceptionElevator();
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
            $this->elevator->raiseException($exception, $busName);
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
