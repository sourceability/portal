<?php

declare(strict_types=1);

namespace Sourceability\Portal;

use Sourceability\Portal\Completer\Completer;
use Sourceability\Portal\Exception\Exception;
use Sourceability\Portal\Result\CastResult;
use Sourceability\Portal\Result\TransferResult;
use Sourceability\Portal\Spell\Spell;

class Portal
{
    public function __construct(
        private readonly Completer $completer,
    ) {
    }

    /**
     * @template TInput
     * @template TOutput
     * @param Spell<TInput, TOutput> $spell
     * @param TInput $input
     * @return CastResult<TOutput>
     */
    public function cast(Spell $spell, mixed $input): CastResult
    {
        $prompt = $spell->getPrompt($input);

        $portalResult = $this->transfer(
            $spell->getSchema(),
            $prompt
        );

        return new CastResult(
            $prompt,
            $portalResult->completion,
            $spell->transcribe(
                $portalResult->value
            ),
            $portalResult->value
        );
    }

    /**
     * @template TInput
     * @template TOutput
     * @param Spell<TInput, TOutput> $spell
     * @return callable(TInput): TOutput
     */
    public function callableFromSpell(Spell $spell): callable
    {
        return fn (mixed $input): mixed => $this->cast($spell, $input)->value;
    }

    public function transfer(mixed $promptSchema, string $prompt, string $schemaType = 'json-schema'): TransferResult
    {
        if (! is_string($promptSchema)) {
            $promptSchema = json_encode($promptSchema, \JSON_THROW_ON_ERROR);
        }

        $fullPrompt = <<<PROMPT
Write a JSON strictly matching the {$schemaType}.
The JSON MUST be valid with proper escaping of special characters.
Only output JSON, without any other text or markdown.
Do not indent the JSON and use no whitespaces like for example `{"foo":"bar","a":1}`

```{$schemaType}
{$promptSchema}
```

Use the following instructions to fill the JSON:
{$prompt}
PROMPT;

        $completion = $this->completer->complete($fullPrompt);
        $completion = preg_replace('#^\s*```\S*|```\s*$#', '', trim($completion));

        if ($completion === null) {
            throw new Exception('No completion');
        }

        $completionValue = json_decode($completion, true, 512, JSON_THROW_ON_ERROR);

        return new TransferResult(
            $prompt,
            $completion,
            $completionValue
        );
    }
}
