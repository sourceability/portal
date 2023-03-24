<?php

declare(strict_types=1);

namespace Sourceability\Portal\Spell;

use JsonSerializable;

/**
 * @template TInput
 * @template TOutput
 */
interface Spell
{
    /**
     * @return array<TInput>
     */
    public function getExamples(): array;

    /**
     * @return string|array<string, mixed>|JsonSerializable The JSON-Schema of the desired completion output.
     */
    public function getSchema(): string|array|JsonSerializable;

    /**
     * @param TInput $input
     */
    public function getPrompt($input): string;

    /**
     * @param array<mixed> $completionValue
     * @return array<TOutput>
     */
    public function transcribe(array $completionValue): array;
}
