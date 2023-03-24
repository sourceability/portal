<?php

declare(strict_types=1);

namespace Sourceability\Portal\ApiPlatformDemo;

use ApiPlatform\Metadata\ApiProperty;

class Part
{
    public function __construct(
        public ?string $mpn,
        public ?string $manufacturer,
        #[ApiProperty(description: 'The electronic component type, like "Fixed Resistor" or "Ceramic Capacitor".')]
        public ?string $category,
        #[ApiProperty(jsonSchemaContext: [
            'enum' => ['active', 'eol', 'unknown'],
        ])]
        public ?string $lifeCycleStatus,
        #[ApiProperty(
            description: 'The individual technical attributes, for example {"Rated Voltage":"22V","Tolerance":"3%"}.',
            jsonSchemaContext: [
                'type' => ['object', 'null'],
            ]
        )]
        public ?array $parameters,
    ) {
    }
}
