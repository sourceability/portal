<?php

declare(strict_types=1);

namespace Sourceability\Portal\Result;

use Sourceability\OpenAIClient\Pricing\ResponseCost;

class TransferResult
{
    public function __construct(
        private readonly string $prompt,
        private readonly string $completion,
        private readonly mixed $value,
        private readonly ResponseCost $cost
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

    public function getCost(): ResponseCost
    {
        return $this->cost;
    }
}
