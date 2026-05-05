<?php

declare(strict_types=1);

namespace App\Domain\PolicyKnowledgeBase\UseCases;

use App\Domain\PolicyKnowledgeBase\Services\ChunkPolicyDocument;
use App\Domain\PolicyKnowledgeBase\Services\RecordPolicyAuditEvent;
use App\Models\PolicyDocument;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final readonly class UpsertPolicyDocument
{
    public function __construct(
        private ChunkPolicyDocument $chunkPolicyDocument,
        private RecordPolicyAuditEvent $recordPolicyAuditEvent,
    ) {}

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function handle(User $actor, int $workspaceId, array $attributes, ?PolicyDocument $policyDocument = null): PolicyDocument
    {
        /** @var PolicyDocument $updated */
        $updated = DB::transaction(function () use ($actor, $workspaceId, $attributes, $policyDocument): PolicyDocument {
            $isNew = ! $policyDocument instanceof PolicyDocument;
            $document = $policyDocument ?? new PolicyDocument();

            $document->fill([
                'workspace_id' => $workspaceId,
                'title' => $attributes['title'] ?? $document->title,
                'content_text' => $attributes['content_text'] ?? $document->content_text,
                'type' => $attributes['type'] ?? $document->type ?? 'text',
                'status' => $document->status ?: PolicyDocument::STATUS_ACTIVE,
                'created_by' => $document->created_by ?? $actor->id,
                'updated_by' => $actor->id,
            ]);

            if ($document->status === '') {
                $document->status = PolicyDocument::STATUS_ACTIVE;
            }

            $document->save();

            if (array_key_exists('content_text', $attributes) || $isNew) {
                $document->chunks()->delete();

                foreach ($this->chunkPolicyDocument->handle((string) $document->content_text) as $chunk) {
                    $document->chunks()->create([
                        'workspace_id' => $workspaceId,
                        'chunk_index' => $chunk['chunk_index'],
                        'chunk_text' => $chunk['chunk_text'],
                        'metadata_json' => $chunk['metadata_json'],
                    ]);
                }
            }

            $this->recordPolicyAuditEvent->handle($isNew ? 'created' : 'updated', $document, $actor);

            return $document->refresh();
        });

        return $updated;
    }
}
