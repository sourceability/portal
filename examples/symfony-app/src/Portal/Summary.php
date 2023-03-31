<?php

declare(strict_types=1);

namespace App\Portal;

use ApiPlatform\Metadata\ApiProperty;

class Summary
{
    public function __construct(
        #[ApiProperty(
            description: 'A unicode emoji that summarize the content.',
            schema: [
                'minLength' => 1,
                'maxLength' => 1,
            ]
        )]
        public readonly string $emoji,
    ) {
    }
}
