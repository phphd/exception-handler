<?php

declare(strict_types=1);

use PhpCsFixer\Fixer\PhpUnit\PhpUnitStrictFixer;
use PhPhD\CodingStandard\ValueObject\Set\PhdSetList;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return static function (ECSConfig $ecsConfig): void {
    $ecsConfig->sets([PhdSetList::ecs()->getPath()]);

    $ecsConfig->paths([__DIR__.'/']);
    $ecsConfig->skip([__DIR__.'/vendor']);

    $ecsConfig->skip([
        PhpUnitStrictFixer::class => [__DIR__.'/tests/Unit/ExceptionHandlingResultFilterMiddlewareTest.php'],
    ]);
};
