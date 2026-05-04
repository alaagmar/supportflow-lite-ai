<?php

declare(strict_types=1);

namespace App\Domain\AiProcessing\UseCases;

use App\Domain\AiProcessing\Contracts\AiProvider;
use App\Models\AiRun;
use App\Models\Ticket;
use App\Models\TicketAiOutput;
use RuntimeException;
use Throwable;

final readonly class ProcessTicketAiPipeline
{
    public function __construct(private AiProvider $provider) {}

    public function handle(int $ticketId): void
    {
        /** @var Ticket|null $ticket */
        $ticket = Ticket::query()->with('aiOutput')->find($ticketId);

        if (! $ticket instanceof Ticket) {
            return;
        }

        if ($ticket->status === Ticket::STATUS_NEEDS_REVIEW && $ticket->aiOutput !== null) {
            return;
        }

        $ticket->forceFill([
            'status' => Ticket::STATUS_PROCESSING,
        ])->save();

        $classificationRun = $this->startRun($ticket, AiRun::TASK_CLASSIFY_TICKET, [
            'ticket' => $this->ticketPayload($ticket),
        ]);

        $draftRun = null;

        try {
            $classificationStartedAt = microtime(true);
            $classification = $this->validatedClassification(
                $this->provider->classifyTicket($this->ticketPayload($ticket)),
            );

            $this->completeRun($classificationRun, $classification, $classificationStartedAt);

            $draftRun = $this->startRun($ticket, AiRun::TASK_DRAFT_REPLY, [
                'ticket' => $this->ticketPayload($ticket),
                'classification' => $classification,
            ]);

            $draftStartedAt = microtime(true);
            $draft = $this->validatedDraft(
                $this->provider->draftReply($this->ticketPayload($ticket), []),
            );

            $this->completeRun($draftRun, $draft, $draftStartedAt);

            TicketAiOutput::query()->updateOrCreate(
                [
                    'workspace_id' => $ticket->workspace_id,
                    'ticket_id' => $ticket->id,
                ],
                [
                    'classification_run_id' => $classificationRun->id,
                    'draft_run_id' => $draftRun->id,
                    'summary' => $classification['summary'],
                    'category' => $classification['category'],
                    'urgency' => $classification['urgency'],
                    'sentiment' => $classification['sentiment'],
                    'language' => $classification['language'],
                    'draft_reply' => $draft['draft_reply'],
                    'recommended_action' => $draft['recommended_action'],
                    'requires_human_approval' => $draft['requires_human_approval'],
                    'confidence' => $draft['confidence'],
                    'evidence_json' => $draft['evidence'],
                ],
            );

            $ticket->forceFill([
                'status' => Ticket::STATUS_NEEDS_REVIEW,
                'category' => $classification['category'],
                'urgency' => $classification['urgency'],
                'sentiment' => $classification['sentiment'],
                'language' => $classification['language'],
                'confidence' => $classification['confidence'],
            ])->save();
        } catch (Throwable $exception) {
            $this->failRunIfRunning($classificationRun, $exception);

            if ($draftRun instanceof AiRun) {
                $this->failRunIfRunning($draftRun, $exception);
            }

            $ticket->forceFill([
                'status' => Ticket::STATUS_FAILED,
            ])->save();

            throw $exception;
        }
    }

    /**
     * @param  array<string, mixed>  $input
     */
    private function startRun(Ticket $ticket, string $taskType, array $input): AiRun
    {
        /** @var AiRun $run */
        $run = AiRun::query()->create([
            'workspace_id' => $ticket->workspace_id,
            'ticket_id' => $ticket->id,
            'provider' => $this->provider->provider(),
            'model' => $this->provider->model(),
            'task_type' => $taskType,
            'status' => AiRun::STATUS_RUNNING,
            'input_json' => $input,
            'started_at' => now(),
        ]);

        return $run;
    }

    /**
     * @param  array<string, mixed>  $output
     */
    private function completeRun(AiRun $run, array $output, float $startedAt): void
    {
        $run->forceFill([
            'status' => AiRun::STATUS_COMPLETED,
            'output_json' => $output,
            'confidence' => $output['confidence'] ?? null,
            'latency_ms' => $this->latencyMilliseconds($startedAt),
            'completed_at' => now(),
        ])->save();
    }

    private function failRunIfRunning(AiRun $run, Throwable $exception): void
    {
        if ($run->status !== AiRun::STATUS_RUNNING) {
            return;
        }

        $run->forceFill([
            'status' => AiRun::STATUS_FAILED,
            'error_message' => $exception->getMessage(),
            'completed_at' => now(),
        ])->save();
    }

    private function latencyMilliseconds(float $startedAt): int
    {
        return (int) round((microtime(true) - $startedAt) * 1000);
    }

    /**
     * @return array{customer_name: string, customer_email: string, subject: string, body: string}
     */
    private function ticketPayload(Ticket $ticket): array
    {
        return [
            'customer_name' => $ticket->customer_name,
            'customer_email' => $ticket->customer_email,
            'subject' => $ticket->subject,
            'body' => $ticket->body,
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array{category: string, urgency: string, sentiment: string, language: string, summary: string, confidence: float}
     */
    private function validatedClassification(array $payload): array
    {
        return [
            'category' => $this->requiredString($payload, 'category'),
            'urgency' => $this->requiredString($payload, 'urgency'),
            'sentiment' => $this->requiredString($payload, 'sentiment'),
            'language' => $this->requiredString($payload, 'language'),
            'summary' => $this->requiredString($payload, 'summary'),
            'confidence' => $this->requiredConfidence($payload, 'confidence'),
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array{draft_reply: string, recommended_action: string, requires_human_approval: bool, confidence: float, evidence: array<int, array<string, mixed>>}
     */
    private function validatedDraft(array $payload): array
    {
        return [
            'draft_reply' => $this->requiredString($payload, 'draft_reply'),
            'recommended_action' => $this->requiredString($payload, 'recommended_action'),
            'requires_human_approval' => $this->requiredBool($payload, 'requires_human_approval'),
            'confidence' => $this->requiredConfidence($payload, 'confidence'),
            'evidence' => $this->requiredEvidenceArray($payload, 'evidence'),
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function requiredString(array $payload, string $key): string
    {
        $value = $payload[$key] ?? null;

        if (! is_string($value) || trim($value) === '') {
            throw new RuntimeException("AI provider response is missing a valid '{$key}' string.");
        }

        return trim($value);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function requiredBool(array $payload, string $key): bool
    {
        $value = $payload[$key] ?? null;

        if (! is_bool($value)) {
            throw new RuntimeException("AI provider response is missing a valid '{$key}' boolean.");
        }

        return $value;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function requiredConfidence(array $payload, string $key): float
    {
        $value = $payload[$key] ?? null;

        if (! is_numeric($value)) {
            throw new RuntimeException("AI provider response is missing a valid '{$key}' numeric value.");
        }

        $confidence = (float) $value;

        if ($confidence < 0 || $confidence > 1) {
            throw new RuntimeException("AI provider '{$key}' must be between 0 and 1.");
        }

        return $confidence;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<int, array<string, mixed>>
     */
    private function requiredEvidenceArray(array $payload, string $key): array
    {
        $value = $payload[$key] ?? null;

        if (! is_array($value)) {
            throw new RuntimeException("AI provider response is missing a valid '{$key}' array.");
        }

        /** @var array<int, array<string, mixed>> $evidence */
        $evidence = array_values($value);

        return $evidence;
    }
}
