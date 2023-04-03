<?php

declare(strict_types=1);

namespace Sourceability\Portal\Completer;

use Sourceability\OpenAIClient\Pricing\ResponseCost;

class Completion
{
    public function __construct(
        private readonly string $completion,
        private readonly ResponseCost $cost,
    ) {
    }

    public function getCompletion(): string
    {
        return $this->completion;
    }

    public function getCost(): ResponseCost
    {
        return $this->cost;
    }
}
