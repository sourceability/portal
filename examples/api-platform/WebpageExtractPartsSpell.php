<?php

declare(strict_types=1);

namespace Sourceability\Portal\ApiPlatformDemo;

use ApiPlatform\JsonSchema\SchemaFactoryInterface;
use Sourceability\Portal\Spell\Spell;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use function Symfony\Component\String\u;

/**
 * @implements Spell<string, Part>
 */
class WebpageExtractPartsSpell extends ApiPlatformSpell
{
    public function __construct(SchemaFactoryInterface $schemaFactory, DenormalizerInterface $denormalizer)
    {
        parent::__construct($schemaFactory, $denormalizer, Part::class);
    }

    public function getExamples(): array
    {
        return [
            u(
                file_get_contents(__DIR__ . '/../webpage.txt')
            )->truncate(3000, '...'),
        ];
    }

    public function getPrompt($input): string
    {
        return <<<PROMPT
Extract all parts mentioned in the following webpage text.
Accuracy is very important. DO NOT GUESS any value.

```txt
${input}
```
PROMPT
        ;
    }
}
