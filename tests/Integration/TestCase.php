<?php

declare(strict_types=1);

namespace PhPhD\ExceptionHandlerBundle\Tests\Integration;

use Nyholm\BundleTest\TestKernel;
use PhPhD\ExceptionHandlerBundle\PhdExceptionHandlerBundle;
use PhPhD\ExceptionHandlerBundle\Tests\Integration\Bootstrap\Compiler\TestServicesCompilerPass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpKernel\KernelInterface;

abstract class TestCase extends KernelTestCase
{
    protected static function getKernelClass(): string
    {
        return TestKernel::class;
    }

    /** @param array<array-key,mixed> $options */
    protected static function createKernel(array $options = []): KernelInterface
    {
        /** @var TestKernel $kernel */
        $kernel = parent::createKernel($options);
        $kernel->addTestBundle(PhdExceptionHandlerBundle::class);
        $kernel->addTestConfig(__DIR__.'/Bootstrap/config/messenger.yaml');
        $kernel->addTestConfig(__DIR__.'/Bootstrap/config/services.yaml');
        $kernel->addTestCompilerPass(new TestServicesCompilerPass());

        return $kernel;
    }
}
