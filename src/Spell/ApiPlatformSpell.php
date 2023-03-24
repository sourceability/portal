<?php

declare(strict_types=1);

namespace Sourceability\Portal\Spell;

use ApiPlatform\JsonSchema\SchemaFactoryInterface;
use JsonSerializable;
use ReflectionClass;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * @template TInput
 * @template TOutput of object
 * @implements Spell<TInput, TOutput>
 */
abstract class ApiPlatformSpell implements Spell
{
    /**
     * @param class-string $class
     */
    public function __construct(
        private readonly SchemaFactoryInterface $schemaFactory,
        private readonly DenormalizerInterface $denormalizer,
        private readonly string $class
    ) {
    }

    public function getSchema(): string|array|JsonSerializable
    {
        $definition = (new ReflectionClass($this->class))->getShortName();
        $schema = $this->schemaFactory->buildSchema($this->class)->getDefinitions()[$definition];

        return json_encode($schema, JSON_THROW_ON_ERROR);
    }

    public function transcribe(array $completionValue): array
    {
        $objects = $this->denormalizer->denormalize(
            $completionValue,
            sprintf('%s[]', $this->class),
            'json'
        );
        assert(is_array($objects));

        return $objects;
    }
}
