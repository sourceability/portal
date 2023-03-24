<?php

declare(strict_types=1);

namespace Sourceability\Portal\Demo;

class Scientist
{
    /**
     * @param array<'sports'|'board games'|'dnd'|'nature'|'art'> $hobbies
     */
    public function __construct(
        public readonly string $firstName,
        public readonly string $lastName,
        public readonly array $hobbies = [],
        public readonly ?int $age = null,
    ) {
    }
}
