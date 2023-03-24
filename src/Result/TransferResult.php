<?php

declare(strict_types=1);

namespace Sourceability\Portal\Result;

class TransferResult
{
    /**
     * @param array<mixed> $value
     */
    public function __construct(
        public readonly string $prompt,
        public readonly string $completion,
        public readonly array $value
    ) {
    }
}
