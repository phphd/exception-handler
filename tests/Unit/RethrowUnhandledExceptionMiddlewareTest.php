<?php

declare(strict_types=1);

namespace PhPhD\ExceptionHandler\Bundle\Tests\Unit;

use LogicException;
use PhPhD\ExceptionHandler\Middleware\Rethrow\RethrowUnhandledExceptionMiddleware;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBus;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;

/**
 * @covers \PhPhD\ExceptionHandler\Middleware\Rethrow\RethrowUnhandledExceptionMiddleware
 *
 * @internal
 */
final class RethrowUnhandledExceptionMiddlewareTest extends TestCase
{
    private MessageBus $messageBus;

    private MiddlewareInterface&MockObject $nextMiddleware;

    protected function setUp(): void
    {
        parent::setUp();

        $this->nextMiddleware = $this->createMock(MiddlewareInterface::class);

        $this->messageBus = new MessageBus([new RethrowUnhandledExceptionMiddleware(), $this->nextMiddleware]);
    }

    public function testReturnsResult(): void
    {
        $envelope = Envelope::wrap(new LogicException());

        $expectResultEnvelope = $this->withResultEnvelope(
            $envelope->with(new HandledStamp(null, 'test')),
        );

        $actualResultEnvelope = $this->messageBus->dispatch($envelope);

        self::assertSame($expectResultEnvelope, $actualResultEnvelope);
    }

    public function testRethrowsExceptionIfNoHandledStampIsPresent(): void
    {
        $envelope = Envelope::wrap($originException = new RuntimeException('whoops'));

        $this->withResultEnvelope($envelope);

        $this->expectExceptionObject($originException);

        $this->messageBus->dispatch($envelope);
    }

    private function withResultEnvelope(Envelope $expectedResultEnvelope): Envelope
    {
        $this->nextMiddleware
            ->method('handle')
            ->willReturn($expectedResultEnvelope)
        ;

        return $expectedResultEnvelope;
    }
}
