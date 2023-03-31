<?php

namespace Sourceability\Portal\Tests\Bundle\SampleApp\Spells\DTO;

class ReviewComment
{
    /**
     * @param 'overall_approach'|'commit_message'|array{path: string, line: int} $context
     */
    public function __construct(
        public readonly string|array $context,
        public readonly string $comment,
        public readonly string $emoji,
    ) {}
}
