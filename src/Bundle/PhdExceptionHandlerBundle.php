<?php

declare(strict_types=1);

namespace PhPhD\ExceptionHandler\Bundle;

use Override;
use PhPhD\ExceptionHandler\Bundle\DependencyInjection\PhdExceptionHandlerExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/** @api */
final class PhdExceptionHandlerBundle extends Bundle
{
    #[Override]
    protected function createContainerExtension(): PhdExceptionHandlerExtension
    {
        return new PhdExceptionHandlerExtension();
    }
}
