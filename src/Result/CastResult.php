<?php

declare(strict_types=1);

namespace Sourceability\Portal\Result;

/**
 * @template TOutput
 */
class CastResult
{
    /**
     * @param array<TOutput> $value
     * @param array<mixed> $transferValue
     */
    public function __construct(
        public readonly string $prompt,
        public readonly string $completion,
        public readonly array $value,
        public readonly array $transferValue
    ) {
    }
}
