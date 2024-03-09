<?php

declare(strict_types=1);

namespace PhPhD\ExceptionHandlerBundle\Tests\Unit;

use LogicException;
use PhPhD\ExceptionHandler\ExceptionHandlerMiddleware;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBus;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackMiddleware;

/**
 * @covers \PhPhD\ExceptionHandler\ExceptionHandlerMiddleware
 *
 * @internal
 */
final class ExceptionHandlerMiddlewareTest extends TestCase
{
    private StackMiddleware $stack;

    private ExceptionHandlerMiddleware $middleware;

    private MiddlewareInterface&MockObject $nextMiddleware;

    protected function setUp(): void
    {
        parent::setUp();

        $this->nextMiddleware = $this->createMock(MiddlewareInterface::class);

        $this->middleware = new ExceptionHandlerMiddleware(new MessageBus());
        $this->stack = new StackMiddleware([$this->middleware, $this->nextMiddleware]);
    }

    public function testRequiresBusNameStamp(): void
    {
        $envelope = Envelope::wrap(new stdClass());

        $this->expectException(LogicException::class);

        $this->middleware->handle($envelope, $this->stack);
    }
}
