<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\Class_\InlineConstructorDefaultToPropertyRector;
use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;
use Rector\TypeDeclaration\Rector\Property\TypedPropertyFromAssignsRector;

return static function (RectorConfig $config): void {
    $config->importNames(true);
    $config->paths([
        __DIR__ . '/bin/portal',
        __DIR__ . '/src',
    ]);
    $config->cacheDirectory(sys_get_temp_dir() . '/rector');

    $config->rule(InlineConstructorDefaultToPropertyRector::class);
    $config->ruleWithConfiguration(TypedPropertyFromAssignsRector::class, [
        TypedPropertyFromAssignsRector::INLINE_PUBLIC => true,
    ]);
    $config->sets([
        LevelSetList::UP_TO_PHP_81,
        SetList::TYPE_DECLARATION,
    ]);
};
