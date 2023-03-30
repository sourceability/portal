<?php

declare(strict_types=1);

namespace Sourceability\Portal\Result;

class TransferResult
{
    public function __construct(
        public readonly string $prompt,
        public readonly string $completion,
        public readonly mixed $value
    ) {
    }
}
