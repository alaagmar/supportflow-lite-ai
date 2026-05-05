<?php

declare(strict_types=1);

namespace App\Http\Controllers\Portal\Policies;

use App\Domain\PolicyKnowledgeBase\Services\RecordPolicyAuditEvent;
use App\Domain\PolicyKnowledgeBase\Support\ResolvesWorkspacePolicyAccess;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Portal\Policies\Concerns\ResolvesWorkspacePolicyContext;
use App\Http\Resources\PolicyDocumentResource;
use App\Http\Responses\ApiResponse;
use App\Models\PolicyDocument;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class UnarchivePolicyDocumentController extends Controller
{
    use ResolvesWorkspacePolicyContext;

    public function __invoke(
        Request $request,
        RecordPolicyAuditEvent $recordPolicyAuditEvent,
        ResolvesWorkspacePolicyAccess $workspacePolicyAccess,
        int $workspace,
        int $policy,
    ): JsonResponse {
        $this->authorizePortalAccess($request);

        /** @var User $user */
        $user = $request->user();

        $this->assertWorkspaceAccess($user, $workspace, $request, $workspacePolicyAccess);

        $policyDocument = $this->resolvePolicyDocument($workspace, $policy, $workspacePolicyAccess);

        Gate::authorize('unarchive', $policyDocument);

        $policyDocument->forceFill([
            'status' => PolicyDocument::STATUS_ACTIVE,
            'archived_at' => null,
            'updated_by' => $user->id,
        ])->save();

        $recordPolicyAuditEvent->handle('unarchived', $policyDocument, $user);

        return ApiResponse::resource(new PolicyDocumentResource($policyDocument->refresh()));
    }
}
