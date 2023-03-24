<?php

declare(strict_types=1);

namespace Sourceability\Portal\ApiPlatformDemo;

use ApiPlatform\Metadata\ApiProperty;

class Person
{
    public function __construct(
        public readonly ?string $firstName = null,
        public readonly ?string $lastName = null,
        #[ApiProperty(jsonSchemaContext: [
            'items' => [
                'example' => ['sports', 'board games', 'dnd', 'nature', 'art'],
                'min_items' => 1,
                'max_items' => 2,
            ],
        ])]
        public readonly ?array $hobbies = null,
        #[ApiProperty(jsonSchemaContext: [
            'minimum' => 0,
        ])]
        public readonly ?int $age = null,
    ) {
    }
}
