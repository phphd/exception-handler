<?php

declare(strict_types=1);

namespace PhPhD\ExceptionHandlerBundle\Tests\Integration;

use PhPhD\ExceptionHandlerBundle\Tests\Integration\Stub\Exception\InnerException;
use PhPhD\ExceptionHandlerBundle\Tests\Integration\Stub\Exception\OuterException;
use PhPhD\ExceptionHandlerBundle\Tests\Integration\Stub\LogExceptionMessageHandlerStub;
use stdClass;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\BusNameStamp;
use Symfony\Component\Messenger\Stamp\HandlerArgumentsStamp;
use Throwable;

/**
 * @covers \PhPhD\ExceptionHandlerBundle\PhdExceptionHandlerBundle
 * @covers \PhPhD\ExceptionHandlerBundle\DependencyInjection\PhdExceptionHandlerExtension
 * @covers \PhPhD\ExceptionHandler\ExceptionHandlerMiddleware
 * @covers \PhPhD\ExceptionHandler\Chain\Elevator\RaiseAs
 * @covers \PhPhD\ExceptionHandler\Chain\Elevator\RaiseAsExceptionElevator
 * @covers \PhPhD\ExceptionHandler\Rethrow\RethrowUnhandledExceptionMiddleware
 *
 * @internal
 */
final class ExceptionHandlerTest extends TestCase
{
    private MessageBusInterface $commandBus;

    private MessageBusInterface $queryBus;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var MessageBusInterface $commandBus */
        $commandBus = self::getContainer()->get('command.bus');
        $this->commandBus = $commandBus;

        /** @var MessageBusInterface $queryBus */
        $queryBus = self::getContainer()->get('query.bus');
        $this->queryBus = $queryBus;
    }

    public function testRaisesNewExceptionFromMapping(): void
    {
        $message = new stdClass();
        $innerException = new InnerException();

        $this->expectException(OuterException::class);

        try {
            $this->queryBus->dispatch($message, [new HandlerArgumentsStamp([$innerException])]);
        } catch (OuterException $e) {
            self::assertSame($innerException, $e->getPrevious());
            self::assertWasLogged($innerException, $message);

            throw $e;
        }
    }

    /**
     * In this case, exception was actually dispatched to handlers,
     * but not a single handler has returned any result nor has thrown an exception.
     */
    public function testRethrowsUnhandledException(): void
    {
        $message = new stdClass();
        $innerException = new InnerException();

        $this->expectException(InnerException::class);

        try {
            $this->commandBus->dispatch($message, [
                new BusNameStamp('api.command.bus'),
                new HandlerArgumentsStamp([$innerException]),
            ]);
        } catch (InnerException $exception) {
            self::assertSame($innerException, $exception);
            self::assertWasLogged($exception, $message);

            throw $exception;
        }
    }

    /** Basically this method verifies that the underlying exception handler was called. */
    private static function assertWasLogged(Throwable $exception, object $message): void
    {
        /** @var LogExceptionMessageHandlerStub $logHandler */
        $logHandler = self::getContainer()->get(LogExceptionMessageHandlerStub::class);
        $loggedExceptions = $logHandler->getLoggedExceptions();

        self::assertSame([[$exception, $message]], $loggedExceptions);
    }
}
