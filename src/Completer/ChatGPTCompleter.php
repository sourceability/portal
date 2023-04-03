<?php

declare(strict_types=1);

namespace Sourceability\Portal\Completer;

use Sourceability\OpenAIClient\Client;
use Sourceability\OpenAIClient\Generated\Model\ChatCompletionRequestMessage;
use Sourceability\OpenAIClient\Generated\Model\CreateChatCompletionRequest;
use Sourceability\OpenAIClient\Generated\Model\CreateChatCompletionResponse;
use Sourceability\OpenAIClient\Pricing\ResponseCostCalculator;
use Sourceability\Portal\Exception\Exception;

class ChatGPTCompleter implements Completer
{
    private readonly CreateChatCompletionRequest $request;

    public function __construct(
        private readonly Client $openAiClient,
        ?CreateChatCompletionRequest $request = null,
        private readonly ResponseCostCalculator $responseCostCalculator = new ResponseCostCalculator()
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

    public function complete(string $prompt): Completion
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

        return new Completion(
            $response->getChoices()[0]->getMessage()->getContent(),
            $this->responseCostCalculator->calculate($response)
        );
    }
}
