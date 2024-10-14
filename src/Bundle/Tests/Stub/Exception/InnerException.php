<?php

declare(strict_types=1);

namespace PhPhD\ExceptionHandler\Bundle\Tests\Stub\Exception;

use PhPhD\ExceptionHandler\Middleware\Chain\Escalator\RaiseAs;
use RuntimeException;

#[RaiseAs(OuterException::class, bus: 'query.exception.bus')]
final class InnerException extends RuntimeException
{
}
