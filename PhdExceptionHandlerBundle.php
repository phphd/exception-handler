<?php

declare(strict_types=1);

namespace PhPhD\ExceptionHandlerBundle;

use PhPhD\ExceptionHandlerBundle\DependencyInjection\PhdExceptionHandlerExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/** @api */
final class PhdExceptionHandlerBundle extends Bundle
{
    /** @override */
    protected function createContainerExtension(): PhdExceptionHandlerExtension
    {
        return new PhdExceptionHandlerExtension();
    }
}
