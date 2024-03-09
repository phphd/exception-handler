<?php

declare(strict_types=1);

namespace PhPhD\ExceptionHandlerBundle\DependencyInjection;

use Exception;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

final class PhdExceptionHandlerExtension extends Extension
{
    public const ALIAS = 'phd_exception_handler';

    /**
     * @param array<array-key,mixed> $configs
     *
     * @override
     *
     * @throws Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        /** @var ?string $env */
        $env = $container->getParameter('kernel.environment');

        $loader = new YamlFileLoader($container, new FileLocator(), $env);
        $loader->load(__DIR__.'/../Resources/config/services.yaml');
    }

    /** @override */
    public function getAlias(): string
    {
        return self::ALIAS;
    }
}
