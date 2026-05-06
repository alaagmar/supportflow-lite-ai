<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\AiProcessing\Contracts\AiProvider;
use App\Domain\AiProcessing\Providers\QwenNvidiaAiProvider;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class QwenAiProviderTest extends TestCase
{
    public function test_it_resolves_qwen_provider_when_configured(): void
    {
        config()->set('ai.provider', 'qwen');
        config()->set('ai.qwen.api_key', 'test-key');

        $provider = app(AiProvider::class);

        $this->assertInstanceOf(QwenNvidiaAiProvider::class, $provider);
    }

    public function test_it_parses_qwen_json_response_for_ticket_classification(): void
    {
        config()->set('ai.provider', 'qwen');
        config()->set('ai.qwen.api_key', 'test-key');

        Http::fake([
            'https://integrate.api.nvidia.com/v1/chat/completions' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => '{"category":"billing","urgency":"high","sentiment":"negative","language":"en","summary":"Customer reports duplicate billing charge.","confidence":0.91}',
                        ],
                    ],
                ],
            ]),
        ]);

        /** @var QwenNvidiaAiProvider $provider */
        $provider = app(AiProvider::class);

        $result = $provider->classifyTicket([
            'subject' => 'Duplicate charge on invoice',
            'body' => 'I was charged twice this month.',
        ]);

        $this->assertSame('billing', $result['category']);
        $this->assertSame('high', $result['urgency']);
        $this->assertSame('negative', $result['sentiment']);
        $this->assertSame('en', $result['language']);
        $this->assertSame('Customer reports duplicate billing charge.', $result['summary']);
        $this->assertSame(0.91, $result['confidence']);
    }
}
