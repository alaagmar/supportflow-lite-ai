<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\AiProcessing\Contracts\AiProvider;
use App\Domain\AiProcessing\Providers\MistralAiProvider;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MistralAiProviderTest extends TestCase
{
    public function test_it_resolves_mistral_provider_when_configured(): void
    {
        config()->set('ai.provider', 'mistral');
        config()->set('ai.mistral.api_key', 'test-key');

        $provider = app(AiProvider::class);

        $this->assertInstanceOf(MistralAiProvider::class, $provider);
    }

    public function test_it_parses_mistral_json_response_for_ticket_classification(): void
    {
        config()->set('ai.provider', 'mistral');
        config()->set('ai.mistral.api_key', 'test-key');

        Http::fake([
            'https://api.mistral.ai/v1/chat/completions' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => '{"category":"billing","urgency":"high","sentiment":"negative","language":"en","summary":"Customer reports duplicate billing charge.","confidence":0.91}',
                        ],
                    ],
                ],
            ]),
        ]);

        /** @var MistralAiProvider $provider */
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

    public function test_it_parses_mistral_response_when_content_is_text_blocks(): void
    {
        config()->set('ai.provider', 'mistral');
        config()->set('ai.mistral.api_key', 'test-key');

        Http::fake([
            'https://api.mistral.ai/v1/chat/completions' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => [
                                [
                                    'type' => 'text',
                                    'text' => "Here is the JSON output:\n```json\n{\"category\":\"access\",\"urgency\":\"medium\",\"sentiment\":\"neutral\",\"language\":\"en\",\"summary\":\"Customer cannot sign in.\",\"confidence\":0.74}\n```",
                                ],
                            ],
                        ],
                    ],
                ],
            ]),
        ]);

        /** @var MistralAiProvider $provider */
        $provider = app(AiProvider::class);

        $result = $provider->classifyTicket([
            'subject' => 'Login issue',
            'body' => 'I cannot access my account',
        ]);

        $this->assertSame('access', $result['category']);
        $this->assertSame('medium', $result['urgency']);
        $this->assertSame(0.74, $result['confidence']);
    }

    public function test_it_parses_mistral_response_with_text_around_json_object(): void
    {
        config()->set('ai.provider', 'mistral');
        config()->set('ai.mistral.api_key', 'test-key');

        Http::fake([
            'https://api.mistral.ai/v1/chat/completions' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => "I analyzed the ticket.\n{\"category\":\"general\",\"urgency\":\"low\",\"sentiment\":\"positive\",\"language\":\"en\",\"summary\":\"Customer shares positive feedback.\",\"confidence\":0.66}\nThanks!",
                        ],
                    ],
                ],
            ]),
        ]);

        /** @var MistralAiProvider $provider */
        $provider = app(AiProvider::class);

        $result = $provider->classifyTicket([
            'subject' => 'Feedback',
            'body' => 'Great support experience',
        ]);

        $this->assertSame('general', $result['category']);
        $this->assertSame('low', $result['urgency']);
        $this->assertSame(0.66, $result['confidence']);
    }

    public function test_it_parses_fenced_draft_reply_json_with_nested_objects(): void
    {
        config()->set('ai.provider', 'mistral');
        config()->set('ai.mistral.api_key', 'test-key');

        Http::fake([
            'https://api.mistral.ai/v1/chat/completions' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => "```json\n{\n  \"draft_reply\": \"Thanks for reaching out. We are checking this now.\",\n  \"recommended_action\": \"Escalate to billing specialist\",\n  \"requires_human_approval\": true,\n  \"confidence\": 0.62,\n  \"evidence\": [\n    {\n      \"source\": \"policy_document\",\n      \"note\": \"Refund policy section 2.1\"\n    },\n    {\n      \"source\": \"ai_model\",\n      \"note\": \"Customer reported duplicate charge\"\n    }\n  ]\n}\n```",
                        ],
                    ],
                ],
            ]),
        ]);

        /** @var MistralAiProvider $provider */
        $provider = app(AiProvider::class);

        $result = $provider->draftReply([
            'subject' => 'Billing issue',
            'body' => 'I was billed twice this month.',
            'customer_name' => 'Jane Doe',
        ], []);

        $this->assertSame('Escalate to billing specialist', $result['recommended_action']);
        $this->assertTrue($result['requires_human_approval']);
        $this->assertSame(0.62, $result['confidence']);
        $this->assertCount(2, $result['evidence']);
    }

    public function test_it_skips_non_json_brace_blocks_before_valid_json(): void
    {
        config()->set('ai.provider', 'mistral');
        config()->set('ai.mistral.api_key', 'test-key');

        Http::fake([
            'https://api.mistral.ai/v1/chat/completions' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => "Format hint: {draft_reply}.\n\n```json\n{\"draft_reply\":\"We are reviewing your ticket.\",\"recommended_action\":\"Assign to support\",\"requires_human_approval\":true,\"confidence\":0.58,\"evidence\":[{\"source\":\"ai_model\",\"note\":\"Customer asks for update\"}]}\n```",
                        ],
                    ],
                ],
            ]),
        ]);

        /** @var MistralAiProvider $provider */
        $provider = app(AiProvider::class);

        $result = $provider->draftReply([
            'subject' => 'Update request',
            'body' => 'Any update on my case?',
            'customer_name' => 'John Doe',
        ], []);

        $this->assertSame('Assign to support', $result['recommended_action']);
        $this->assertSame(0.58, $result['confidence']);
    }

    public function test_it_parses_json_with_trailing_commas(): void
    {
        config()->set('ai.provider', 'mistral');
        config()->set('ai.mistral.api_key', 'test-key');

        Http::fake([
            'https://api.mistral.ai/v1/chat/completions' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => '{"category":"general","urgency":"low","sentiment":"neutral","language":"en","summary":"Customer asks a generic question.","confidence":0.7,}',
                        ],
                    ],
                ],
            ]),
        ]);

        /** @var MistralAiProvider $provider */
        $provider = app(AiProvider::class);

        $result = $provider->classifyTicket([
            'subject' => 'General question',
            'body' => 'How long does setup take?',
        ]);

        $this->assertSame('general', $result['category']);
        $this->assertSame(0.7, $result['confidence']);
    }

    public function test_it_sends_strict_json_schema_for_draft_reply_requests(): void
    {
        config()->set('ai.provider', 'mistral');
        config()->set('ai.mistral.api_key', 'test-key');

        Http::fake(function (Request $request) {
            $payload = $request->data();

            $this->assertSame('json_schema', data_get($payload, 'response_format.type'));
            $this->assertSame('ticket_draft_reply', data_get($payload, 'response_format.json_schema.name'));
            $this->assertTrue((bool) data_get($payload, 'response_format.json_schema.strict'));

            $requiredKeys = data_get($payload, 'response_format.json_schema.schema.required');
            $this->assertSame(
                ['draft_reply', 'recommended_action', 'requires_human_approval', 'confidence', 'evidence'],
                $requiredKeys
            );

            return Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => '{"draft_reply":"Thanks for contacting us.","recommended_action":"Escalate to billing specialist","requires_human_approval":true,"confidence":0.67,"evidence":[{"source":"policy_document","note":"Refund policy section 2.1"}]}',
                        ],
                    ],
                ],
            ]);
        });

        /** @var MistralAiProvider $provider */
        $provider = app(AiProvider::class);

        $result = $provider->draftReply([
            'subject' => 'Billing issue',
            'body' => 'I was charged twice this month.',
            'customer_name' => 'Jane Doe',
        ], []);

        $this->assertSame('Thanks for contacting us.', $result['draft_reply']);
    }
}
