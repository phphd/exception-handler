<?php

declare(strict_types=1);

namespace PhPhD\ExceptionHandler\Bundle\Tests\Unit;

use PhPhD\ExceptionHandler\Middleware\Result\ExceptionHandlerResultFilterMiddleware;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBus;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;

/**
 * @covers \PhPhD\ExceptionHandler\Middleware\Result\ExceptionHandlerResultFilterMiddleware
 *
 * @internal
 */
final class ExceptionHandlingResultFilterMiddlewareTest extends TestCase
{
    private MessageBus $messageBus;

    private MiddlewareInterface&MockObject $nextMiddleware;

    protected function setUp(): void
    {
        parent::setUp();

        $this->nextMiddleware = $this->createMock(MiddlewareInterface::class);

        $this->messageBus = new MessageBus([new ExceptionHandlerResultFilterMiddleware(), $this->nextMiddleware]);
    }

    public function testPassesMessageThrough(): void
    {
        $envelope = $this->withResultEnvelope(Envelope::wrap(new stdClass()));

        $resultEnvelope = $this->messageBus->dispatch($envelope);

        self::assertEquals($envelope, $resultEnvelope);
    }

    public function testKeepsOnlyNonNullResultStamps(): void
    {
        $envelope = Envelope::wrap(new stdClass());

        $this->withResultEnvelope(
            $envelope
                ->with(
                    $firstResult = new HandledStamp('some result', 'test'),
                    $secondResult = new HandledStamp(null, 'test'),
                    $thirdResult = new HandledStamp('another result', 'test'),
                ),
        );

        $actualResultEnvelope = $this->messageBus->dispatch($envelope);

        self::assertSame([$firstResult, $thirdResult], $actualResultEnvelope->all(HandledStamp::class));
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
