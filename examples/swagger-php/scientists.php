<?php

declare(strict_types=1);

use OpenApi\Generator;
use Sourceability\OpenAIClient\Client;
use Sourceability\Portal\Completer\ChatGPTCompleter;
use Sourceability\Portal\Portal;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\ConsoleOutput;

require __DIR__ . '/vendor/autoload.php';

$openapi = Generator::scan([__DIR__ . '/User.php'], [
    'logger' => new ConsoleLogger(new ConsoleOutput(ConsoleOutput::VERBOSITY_QUIET)),
]);

$portal = new Portal(
    new ChatGPTCompleter(
        Client::create(
            apiKey: getenv('OPENAI_API_KEY')
        )
    )
);

$result = $portal->transfer(
    $openapi->ref('#/components/schemas/User'),
    $prompt = <<<PROMPT
The 10 most famous computer scientists
PROMPT
);

dump(
    $prompt,
    $result,
);
