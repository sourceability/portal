<?php

declare(strict_types=1);

namespace Sourceability\Portal\Result;

class TransferResult
{
    public function __construct(
        private readonly string $prompt,
        private readonly string $completion,
        private readonly mixed $value
    ) {
    }

    public function getPrompt(): string
    {
        return $this->prompt;
    }

    public function getCompletion(): string
    {
        return $this->completion;
    }

    public function getValue(): mixed
    {
        return $this->value;
    }
}
