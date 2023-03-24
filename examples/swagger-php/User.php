<?php

declare(strict_types=1);

namespace Sourceability\Portal\SwaggerDemo;

use OpenApi\Attributes as OA;

#[OA\Schema]
class User
{
    public function __construct(
        #[OA\Property]
        public readonly ?string $firstName = null,
        #[OA\Property]
        public readonly ?string $lastName = null,
        #[OA\Property(
            items: new OA\Items(
                enum: ['sports', 'board games', 'dnd', 'nature', 'art']
            ),
            minItems: 1,
            maxItems: 2
        )]
        public readonly ?array $hobbies = null,
        #[OA\Property(minimum: 0)]
        public readonly ?int $age = null,
    ) {
    }
}
