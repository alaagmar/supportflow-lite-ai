<?php

declare(strict_types=1);

namespace App\Http\Controllers\Portal\Policies;

use App\Domain\PolicyKnowledgeBase\Support\ResolvesWorkspacePolicyAccess;
use App\Domain\PolicyKnowledgeBase\UseCases\UpsertPolicyDocument;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Portal\Policies\Concerns\ResolvesWorkspacePolicyContext;
use App\Http\Requests\Policies\UpdatePolicyDocumentRequest;
use App\Http\Resources\PolicyDocumentResource;
use App\Http\Responses\ApiResponse;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class UpdatePolicyDocumentController extends Controller
{
    use ResolvesWorkspacePolicyContext;

    public function __invoke(
        UpdatePolicyDocumentRequest $request,
        UpsertPolicyDocument $upsertPolicyDocument,
        ResolvesWorkspacePolicyAccess $workspacePolicyAccess,
        int $workspace,
        int $policy,
    ): JsonResponse {
        $this->authorizePortalAccess($request);

        /** @var User $user */
        $user = $request->user();

        $this->assertWorkspaceAccess($user, $workspace, $request, $workspacePolicyAccess);

        $policyDocument = $this->resolvePolicyDocument($workspace, $policy, $workspacePolicyAccess);

        Gate::authorize('update', $policyDocument);

        /** @var array<string, mixed> $validated */
        $validated = $request->validated();

        $updated = $upsertPolicyDocument->handle($user, $workspace, $validated, $policyDocument);

        return ApiResponse::resource(new PolicyDocumentResource($updated));
    }
}
