<?php

declare(strict_types=1);

namespace Sourceability\Portal\Spell;

use JsonSerializable;

/**
 * @implements Spell<mixed, mixed>
 */
class StaticSpell implements Spell
{
    /**
     * @param array<string, mixed> $schema
     * @param array<int, mixed> $examples
     */
    public function __construct(
        private readonly array $schema,
        private readonly array $examples,
        private readonly string $prompt
    ) {
    }

    public function getExamples(): array
    {
        return $this->examples;
    }

    public function getSchema(): string|array|JsonSerializable
    {
        return $this->schema;
    }

    public function getPrompt($input): string
    {
        $replace = [
            '{{ input }}' => ! is_string($input) ? json_encode($input, JSON_THROW_ON_ERROR) : $input,
        ];
        if (is_array($input)) {
            foreach ($input as $key => $value) {
                if (! is_string($value)) {
                    $value = json_encode($value, JSON_THROW_ON_ERROR);
                }

                $replace[sprintf('{{ %s }}', $key)] = $value;
            }
        }

        return str_replace(
            array_keys($replace),
            array_values($replace),
            $this->prompt
        );
    }

    public function transcribe(mixed $completionValue): mixed
    {
        return $completionValue;
    }
}
