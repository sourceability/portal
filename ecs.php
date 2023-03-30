<?php

use PhpCsFixer\Fixer\Whitespace\MethodChainingIndentationFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;

return static function (ECSConfig $config): void {
    $config->paths([
        __DIR__ . '/bin/portal',
        __DIR__ . '/src',
        __DIR__ . '/examples/api-platform',
        __DIR__ . '/examples/goldspecdigital-oooas',
        __DIR__ . '/examples/schema-org',
        __DIR__ . '/examples/swagger-php',
        __DIR__ . '/examples/swaggest',
    ]);
    $config->skip([
        __DIR__ . '/examples/*/vendor',
    ]);
    $config->cacheDirectory(sys_get_temp_dir() . '/ecs');

    $config->sets([
        SetList::PSR_12,
        SetList::CLEAN_CODE,
        SetList::ARRAY,
        SetList::COMMON,
        SetList::COMMENTS,
        SetList::CONTROL_STRUCTURES,
        SetList::DOCBLOCK,
        SetList::NAMESPACES,
        SetList::SPACES,
    ]);

    $config->skip([
        MethodChainingIndentationFixer::class => [
            __DIR__ . '/src/Bundle/DependencyInjection/Configuration.php'
        ],
    ]);
};
