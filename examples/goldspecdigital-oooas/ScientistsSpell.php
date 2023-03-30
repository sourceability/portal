<?php

declare(strict_types=1);

namespace Sourceability\Portal\Demo;

use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use JsonSerializable;
use Sourceability\Portal\Spell\Spell;

/**
 * @implements Spell<
 *     string|array{count: int, description: string},
 *     array<Scientist>
 * >
 */
class ScientistsSpell implements Spell
{
    public function getExamples(): array
    {
        return [
            [
                'count' => 3,
                'description' => 'famous computer scientists',
            ],
            [
                'count' => 2,
                'description' => 'last US presidents',
            ],
        ];
    }

    public function getSchema(): string|JsonSerializable
    {
        return Schema::array()
            ->items(
                Schema::object()
                    ->properties(
                        Schema::string('firstName'),
                        Schema::string('lastName'),
                        Schema::array('hobbies')
                            ->items(
                                Schema::string()
                                    ->enum(['sports', 'board games', 'dnd', 'nature', 'art'])
                            )
                            ->minItems(1)
                            ->maxItems(2),
                        Schema::integer('age')->minimum(0)
                    )
                    ->required('firstName', 'lastName')
            )
        ;
    }

    public function getPrompt($input): string
    {
        if (is_string($input)) {
            return $input;
        }

        return sprintf('The %d most %s.', $input['count'], $input['description']);
    }

    public function transcribe(mixed $completionValue): array
    {
        return array_map(
            function (array $value) {
                return new Scientist(
                    firstName: $value['firstName'],
                    lastName: $value['lastName'],
                    hobbies: $value['hobbies'] ?? [],
                    age: $value['age'] ?? null
                );
            },
            $completionValue
        );
    }
}
