<?php

declare(strict_types=1);

namespace PhPhD\ExceptionHandlerBundle\Tests\Integration\Stub\Exception;

use PhPhD\ExceptionHandler\Chain\Elevator\RaiseAs;
use RuntimeException;

#[RaiseAs(OuterException::class, bus: 'query.exception.bus')]
final class InnerException extends RuntimeException
{
}
