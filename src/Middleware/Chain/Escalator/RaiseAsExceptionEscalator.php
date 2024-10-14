<?php

declare(strict_types=1);

namespace PhPhD\ExceptionHandler\Middleware\Chain\Escalator;

use ReflectionAttribute;
use ReflectionClass;
use Throwable;

/** @internal */
final class RaiseAsExceptionEscalator
{
    /** @throws Throwable */
    public function raiseException(Throwable $exception, string $busName): void
    {
        $reflectionClass = new ReflectionClass($exception);

        $attributes = $reflectionClass->getAttributes(RaiseAs::class, ReflectionAttribute::IS_INSTANCEOF);

        if ([] === $attributes) {
            return;
        }

        $this->matchAndRaise($attributes, $busName, $exception);
    }

    /**
     * @param ReflectionAttribute<RaiseAs>[] $raiseAttributes
     *
     * @throws Throwable
     */
    private function matchAndRaise(array $raiseAttributes, string $busName, Throwable $exception): void
    {
        foreach ($raiseAttributes as $raiseAttribute) {
            $attribute = $raiseAttribute->newInstance();

            if (!$attribute->matchesBus($busName)) {
                continue;
            }

            throw $attribute->convertException($exception);
        }
    }
}
