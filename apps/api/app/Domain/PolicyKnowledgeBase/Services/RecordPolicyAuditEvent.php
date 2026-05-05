<?php

declare(strict_types=1);

namespace App\Domain\PolicyKnowledgeBase\Services;

use App\Models\PolicyDocument;
use App\Models\User;
use Illuminate\Support\Facades\Log;

final class RecordPolicyAuditEvent
{
    public function handle(string $action, PolicyDocument $policyDocument, User $actor): void
    {
        Log::info('policy_document.lifecycle', [
            'action' => $action,
            'workspace_id' => $policyDocument->workspace_id,
            'policy_document_id' => $policyDocument->id,
            'actor_user_id' => $actor->id,
        ]);
    }
}
