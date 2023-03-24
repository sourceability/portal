<?php

declare(strict_types=1);

namespace Sourceability\Portal\Completer;

use Sourceability\OpenAIClient\Client;
use Sourceability\OpenAIClient\Generated\Model\ChatCompletionRequestMessage;
use Sourceability\OpenAIClient\Generated\Model\CreateChatCompletionRequest;
use Sourceability\OpenAIClient\Generated\Model\CreateChatCompletionResponse;
use Sourceability\Portal\Exception;

class ChatGPTCompleter implements Completer
{
    private readonly CreateChatCompletionRequest $request;

    public function __construct(
        private readonly Client $openAiClient,
        ?CreateChatCompletionRequest $request = null
    ) {
        if ($request === null) {
            $request = new CreateChatCompletionRequest(
                model: 'gpt-3.5-turbo',
                temperature: 0,
                maxTokens: 2000,
            );
        }
        $this->request = $request;
    }

    public function complete(string $prompt): string
    {
        $chatCompletionRequest = clone $this->request;
        $chatCompletionRequest->setMessages([
            new ChatCompletionRequestMessage(
                role: 'system',
                content: $prompt
            ),
        ]);

        $response = $this->openAiClient->createChatCompletion($chatCompletionRequest);

        if (! $response instanceof CreateChatCompletionResponse) {
            throw new Exception('createChatCompletion did not return a ' . CreateChatCompletionResponse::class);
        }

        return $response->getChoices()[0]->getMessage()->getContent();
    }
}
