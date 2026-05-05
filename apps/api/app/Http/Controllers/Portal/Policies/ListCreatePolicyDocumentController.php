<?php

declare(strict_types=1);

namespace App\Http\Controllers\Portal\Policies;

use App\Domain\PolicyKnowledgeBase\Support\ResolvesWorkspacePolicyAccess;
use App\Domain\PolicyKnowledgeBase\UseCases\UpsertPolicyDocument;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Portal\Policies\Concerns\ResolvesWorkspacePolicyContext;
use App\Http\Requests\Policies\StorePolicyDocumentRequest;
use App\Http\Resources\PolicyDocumentResource;
use App\Http\Responses\ApiResponse;
use App\Models\PolicyDocument;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class ListCreatePolicyDocumentController extends Controller
{
    use ResolvesWorkspacePolicyContext;

    public function __invoke(
        StorePolicyDocumentRequest $request,
        UpsertPolicyDocument $upsertPolicyDocument,
        ResolvesWorkspacePolicyAccess $workspacePolicyAccess,
        int $workspace,
    ): JsonResponse {
        $this->authorizePortalAccess($request);

        /** @var User $user */
        $user = $request->user();

        $this->assertWorkspaceAccess($user, $workspace, $request, $workspacePolicyAccess);

        if ($request->isMethod('get')) {
            Gate::authorize('viewAny', PolicyDocument::class);

            $status = $request->query('status');

            $query = PolicyDocument::query()
                ->where('workspace_id', $workspace)
                ->latest('id');

            if (is_string($status) && in_array($status, PolicyDocument::STATUSES, true)) {
                $query->where('status', $status);
            }

            return ApiResponse::resource(PolicyDocumentResource::collection(
                $query->paginate(min(max($request->integer('per_page', 25), 1), 100))->withQueryString(),
            ));
        }

        Gate::authorize('create', [PolicyDocument::class, $workspace]);

        /** @var array<string, mixed> $validated */
        $validated = $request->validated();

        $policyDocument = $upsertPolicyDocument->handle($user, $workspace, $validated);

        return ApiResponse::resource(new PolicyDocumentResource($policyDocument), Response::HTTP_CREATED);
    }
}
