<?php

declare(strict_types=1);

namespace PhPhD\ExceptionHandlerBundle\Tests\Integration\Bootstrap\Middleware;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\DelayedMessageHandlingException;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Throwable;

/**
 * @api
 *
 * @internal
 */
final class ThrowOriginExceptionMiddleware implements MiddlewareInterface
{
    /** @throws Throwable */
    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        try {
            return $stack->next()->handle($envelope, $stack);
        } catch (DelayedMessageHandlingException|HandlerFailedException $exception) {
            throw $this->getSourceException($exception) ?? $exception;
        }
    }

    private function getSourceException(Throwable $exception): ?Throwable
    {
        $e = $exception->getPrevious();

        while ($e instanceof HandlerFailedException) {
            $e = $e->getPrevious();
        }

        return $e;
    }
}
