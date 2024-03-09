<?php

declare(strict_types=1);

namespace PhPhD\ExceptionHandler\Chain\Elevator;

use ReflectionAttribute;
use ReflectionClass;
use Throwable;

/** @internal */
final class RaiseAsExceptionElevator
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
