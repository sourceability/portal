<?php

declare(strict_types=1);

namespace Sourceability\Portal\Spell;

use ApiPlatform\JsonSchema\Schema;
use ApiPlatform\JsonSchema\SchemaFactoryInterface;
use JsonSerializable;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * @template TInput
 * @template TOutput of object|array<object>
 * @implements Spell<TInput, TOutput>
 */
abstract class ApiPlatformSpell implements Spell
{
    public function __construct(
        private readonly SchemaFactoryInterface $schemaFactory,
        private readonly DenormalizerInterface $denormalizer
    ) {
    }

    public function getExamples(): array
    {
        return []; // Make this method "optional" in subclasses
    }

    public function getSchema(): string|array|JsonSerializable
    {
        $schema = $this->schemaFactory->buildSchema(
            $this->getClass(),
            'json',
            Schema::TYPE_INPUT,
            null,
            null,
            null,
            $this->isCollection()
        );

        $schema = json_decode(json_encode($schema, JSON_THROW_ON_ERROR), true, 512, JSON_THROW_ON_ERROR);
        assert(is_array($schema));

        return $this->minifySchema($schema);
    }

    public function transcribe(mixed $completionValue): mixed
    {
        $type = $this->getClass();
        if ($this->isCollection()) {
            $type .= '[]';
        }

        return $this->denormalizer->denormalize(
            $completionValue,
            $type,
            'json'
        );
    }

    /**
     * @return class-string
     */
    abstract protected function getClass(): string;

    protected function isCollection(): bool
    {
        return false;
    }

    /**
     * @param array<mixed> $schema
     * @return array<mixed>
     */
    private function minifySchema(array $schema): array
    {
        unset($schema['deprecated']);
        if (array_key_exists('description', $schema)
            && is_string($schema['description'])
            && mb_strlen($schema['description']) < 1
        ) {
            unset($schema['description']);
        }

        foreach ($schema as &$value) {
            if (! is_array($value)) {
                continue;
            }

            $value = $this->minifySchema($value);
        }

        return $schema;
    }
}
