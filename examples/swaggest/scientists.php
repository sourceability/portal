<?php

declare(strict_types=1);

use Sourceability\OpenAIClient\Client;
use Sourceability\Portal\Completer\ChatGPTCompleter;
use Sourceability\Portal\Portal;
use Swaggest\JsonSchema\Schema;

require __DIR__ . '/vendor/autoload.php';

$schema = Schema::object()
    ->setProperty('firstName', Schema::string())
    ->setProperty('lastName', Schema::string())
    ->setProperty(
        'hobbies',
        Schema::arr()
            ->setItems(
                Schema::string()
                    ->setEnum(['sports', 'board games', 'dnd', 'nature', 'art'])
                    ->setMinItems(1)
                    ->setMaxItems(2)
            )
    )
    ->setProperty('age', Schema::integer()->setMinimum(0))
    ->setRequired(['firstName', 'lastName'])
;

$portal = new Portal(
    new ChatGPTCompleter(
        Client::create(
            apiKey: getenv('OPENAI_API_KEY')
        )
    )
);

$result = $portal->transfer(
    $schema->jsonSerialize(),
    $prompt = <<<PROMPT
The 10 most famous computer scientists
PROMPT
);

dump(
    $prompt,
    $result,
);
