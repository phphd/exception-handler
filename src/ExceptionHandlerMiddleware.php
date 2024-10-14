<?php

declare(strict_types=1);

namespace PhPhD\ExceptionHandler;

use Exception;
use LogicException;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Stamp\BusNameStamp;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Messenger\Stamp\HandlerArgumentsStamp;
use Symfony\Component\String\ByteString;
use Throwable;

/** @api */
final class ExceptionHandlerMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly MessageBusInterface $exceptionHandlerBus,
    ) {
    }

    /** @throws Throwable */
    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        $busName = $this->getBusName($envelope);
        $message = $envelope->getMessage();

        try {
            return $stack->next()->handle($envelope, $stack);
        } catch (Exception $exception) {
            $exceptionBusName = $this->getExceptionBusName($busName);
            $handledExceptionEnvelope = $this->handleException($exceptionBusName, $exception, $message);

            /** @var list<HandledStamp> $handledExceptionStamps */
            $handledExceptionStamps = $handledExceptionEnvelope->all(HandledStamp::class);

            return $envelope->with(...$handledExceptionStamps);
        }
    }

    private function getBusName(Envelope $envelope): ByteString
    {
        $busNameStamp = $envelope->last(BusNameStamp::class);

        if (null === $busNameStamp) {
            throw new LogicException('Bus name is required');
        }

        return new ByteString($busNameStamp->getBusName());
    }

    private function getExceptionBusName(ByteString $busName): string
    {
        return $busName->trimSuffix('.bus')->append('.exception.bus')->toString();
    }

    private function handleException(string $exceptionBusName, Exception $exception, object $message): Envelope
    {
        return $this->exceptionHandlerBus->dispatch(
            Envelope::wrap($exception, [new BusNameStamp($exceptionBusName)]),
            [new HandlerArgumentsStamp([$message])],
        );
    }
}
