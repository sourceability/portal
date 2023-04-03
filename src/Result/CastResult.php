<?php

declare(strict_types=1);

namespace Sourceability\Portal\Result;

use Sourceability\OpenAIClient\Pricing\ResponseCost;

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
        private readonly mixed $transferValue,
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

    public function getCost(): ResponseCost
    {
        return $this->cost;
    }
}
