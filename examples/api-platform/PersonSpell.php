<?php

declare(strict_types=1);

namespace Sourceability\Portal\ApiPlatformDemo;

use ApiPlatform\JsonSchema\SchemaFactoryInterface;
use Sourceability\Portal\Spell\Spell;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * @implements Spell<string, Person>
 */
class PersonSpell extends ApiPlatformSpell
{
    public function __construct(SchemaFactoryInterface $schemaFactory, DenormalizerInterface $denormalizer)
    {
        parent::__construct($schemaFactory, $denormalizer, Person::class);
    }

    public function getExamples(): array
    {
        return [
            'The 3 most famous computer scientists',
            'The last 2 US presidents',
        ];
    }

    public function getPrompt($input): string
    {
        return $input;
    }
}
