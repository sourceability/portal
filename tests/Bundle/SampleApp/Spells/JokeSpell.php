<?php

namespace Sourceability\Portal\Tests\Bundle\SampleApp\Spells;

use Sourceability\Portal\Spell\Spell;

/**
 * @implements Spell<string, string>
 */
class JokeSpell implements Spell
{
    public function getExamples(): array
    {
        return [
        ];
    }

    public function getSchema(): array
    {
        return [
            'type' => 'string',
        ];
    }

    public function getPrompt($input): string
    {
        return 'Make a short funny joke about ' . json_encode($input);
    }

    public function transcribe(mixed $completionValue): string
    {
        return $completionValue;
    }
}
