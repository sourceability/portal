<?php

declare(strict_types=1);

use Sourceability\OpenAIClient\Client;
use Sourceability\Portal\Completer\ChatGPTCompleter;
use Sourceability\Portal\Demo\ScientistsSpell;
use Sourceability\Portal\Portal;

require __DIR__ . '/vendor/autoload.php';

$portal = new Portal(
    new ChatGPTCompleter(
        Client::create(
            apiKey: getenv('OPENAI_API_KEY')
        )
    )
);

$result = $portal->cast(
    new ScientistsSpell(),
    [
        'count' => 10,
        'description' => 'famous computer scientists',
    ]
);

dump(
    $result->getPrompt(),
    $result->getValue(),
);
