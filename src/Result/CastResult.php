<?php

declare(strict_types=1);

namespace Sourceability\Portal\Result;

/**
 * @template TOutput
 */
class CastResult
{
    /**
     * @param TOutput $value
     */
    public function __construct(
        public readonly string $prompt,
        public readonly string $completion,
        public readonly mixed $value,
        public readonly mixed $transferValue
    ) {
    }
}
