<?php

declare(strict_types=1);

namespace Sourceability\Portal\ApiPlatformDemo;

use ApiPlatform\JsonSchema\SchemaFactoryInterface;
use Sourceability\Portal\Spell\Spell;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * @implements Spell<string, Part>
 */
class PartListSpell extends ApiPlatformSpell
{
    public function __construct(SchemaFactoryInterface $schemaFactory, DenormalizerInterface $denormalizer)
    {
        parent::__construct($schemaFactory, $denormalizer, Part::class);
    }

    public function getExamples(): array
    {
        return [
            'smartwatch',
            'bookshelf speaker',
        ];
    }

    public function getPrompt($input): string
    {
        return sprintf('A list of parts to build a %s.', $input);
    }
}
