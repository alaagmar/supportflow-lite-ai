<?php

declare(strict_types=1);

namespace App\Domain\Identity\Data;

use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Collection;

final readonly class CurrentSessionData
{
    /**
     * @param  Collection<int, Workspace>  $workspaces
     */
    public function __construct(
        public User $user,
        public Collection $workspaces,
    ) {}
}
