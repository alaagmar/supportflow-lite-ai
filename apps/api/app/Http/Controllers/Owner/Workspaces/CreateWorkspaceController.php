<?php

declare(strict_types=1);

namespace App\Http\Controllers\Owner\Workspaces;

use App\Domain\Workspaces\UseCases\CreateWorkspace;
use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\StoreWorkspaceRequest;
use App\Http\Resources\Workspaces\WorkspaceResource;
use App\Http\Responses\ApiResponse;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class CreateWorkspaceController extends Controller
{
    public function __invoke(StoreWorkspaceRequest $request, CreateWorkspace $createWorkspace): JsonResponse
    {
        Gate::authorize('accessOwnerPortal', Workspace::class);
        Gate::authorize('create', Workspace::class);

        /** @var array{name: string} $validated */
        $validated = $request->validated();

        /** @var User $user */
        $user = $request->user();

        return ApiResponse::resource(
            new WorkspaceResource($createWorkspace->handle($user, $validated['name'])),
            Response::HTTP_CREATED,
        );
    }
}
