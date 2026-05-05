<?php

declare(strict_types=1);

namespace App\Http\Controllers\Portal\Policies;

use App\Domain\PolicyKnowledgeBase\UseCases\RetrievePolicyGuidance;
use App\Domain\PolicyKnowledgeBase\Support\ResolvesWorkspacePolicyAccess;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Portal\Policies\Concerns\ResolvesWorkspacePolicyContext;
use App\Http\Requests\Policies\RetrievePolicyGuidanceRequest;
use App\Http\Resources\PolicyRetrievalResultResource;
use App\Http\Responses\ApiResponse;
use App\Models\PolicyDocument;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class RetrievePolicyGuidanceController extends Controller
{
    use ResolvesWorkspacePolicyContext;

    public function __invoke(
        RetrievePolicyGuidanceRequest $request,
        RetrievePolicyGuidance $retrievePolicyGuidance,
        ResolvesWorkspacePolicyAccess $workspacePolicyAccess,
        int $workspace,
    ): JsonResponse {
        $this->authorizePortalAccess($request);

        /** @var User $user */
        $user = $request->user();

        $this->assertWorkspaceAccess($user, $workspace, $request, $workspacePolicyAccess);
        Gate::authorize('retrieve', [PolicyDocument::class, $workspace]);

        /** @var array{query_text: string, category_hint?: string, limit?: int} $validated */
        $validated = $request->validated();

        $data = $retrievePolicyGuidance->handle(
            workspaceId: $workspace,
            queryText: $validated['query_text'],
            categoryHint: $validated['category_hint'] ?? null,
            limit: $validated['limit'] ?? 5,
        );

        return ApiResponse::resource(PolicyRetrievalResultResource::collection($data));
    }
}
