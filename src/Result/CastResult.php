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
        private readonly string $prompt,
        private readonly string $completion,
        private readonly mixed $value,
        private readonly mixed $transferValue
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

    /**
     * @return TOutput
     */
    public function getValue(): mixed
    {
        return $this->value;
    }

    public function getTransferValue(): mixed
    {
        return $this->transferValue;
    }
}
