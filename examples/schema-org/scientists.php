<?php

declare(strict_types=1);

use Sourceability\OpenAIClient\Client;
use Sourceability\Portal\Completer\ChatGPTCompleter;
use Sourceability\Portal\Portal;

require __DIR__ . '/vendor/autoload.php';

$portal = new Portal(
    new ChatGPTCompleter(
        Client::create(
            apiKey: getenv('OPENAI_API_KEY')
        )
    )
);

$result = $portal->transfer(
    '{"@context": "https://schema.org","@type": "Person"}',
    $prompt = <<<PROMPT
The 10 most famous computer scientists.
Fill the most relevant properties from the schema.
```
PROMPT
    ,
    'JSON-LD'
);

dump(
    $prompt,
    $result
);
