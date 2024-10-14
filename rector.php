<?php

declare(strict_types=1);

use PhPhD\CodingStandard\ValueObject\Set\PhdSetList;
use Rector\CodeQuality\Rector\ClassMethod\LocallyCalledStaticMethodToNonStaticRector;
use Rector\Config\RectorConfig;
use Rector\ValueObject\PhpVersion;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([__DIR__.'/']);
    $rectorConfig->skip([__DIR__.'/vendor']);

    $rectorConfig->sets([PhdSetList::rector()->getPath()]);
    $rectorConfig->phpVersion(PhpVersion::PHP_81);

    $rectorConfig->skip([
        LocallyCalledStaticMethodToNonStaticRector::class => [
            __DIR__.'/tests',
            __DIR__.'/src/*/Tests/*.php',
        ],
    ]);
};
