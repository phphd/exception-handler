<?php

declare(strict_types=1);

namespace PhPhD\ExceptionHandler\Middleware\Chain\Escalator;

use Attribute;
use Throwable;

/** @api */
#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
final class RaiseAs
{
    public function __construct(
        /** @var class-string<Throwable> $exception */
        private readonly string $exception,
        private readonly string $bus,
    ) {
    }

    public function matchesBus(string $bus): bool
    {
        return $bus === $this->bus;
    }

    public function convertException(Throwable $exception): Throwable
    {
        $exceptionClass = $this->exception;

        return new $exceptionClass(previous: $exception);
    }
}
