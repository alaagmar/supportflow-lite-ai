<?php

declare(strict_types=1);

namespace App\Domain\Workspaces\UseCases;

use App\Domain\Workspaces\Contracts\WorkspaceRepository;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Support\Facades\DB;

final readonly class CreateWorkspace
{
    public function __construct(private WorkspaceRepository $workspaces) {}

    public function handle(User $owner, string $name): Workspace
    {
        return DB::transaction(fn (): Workspace => $this->workspaces->createOwnedForUser($owner, $name));
    }
}
