<?php

declare(strict_types=1);

namespace App\Domain\Identity\UseCases;

use App\Models\User;

final readonly class LogoutSession
{
    public function handle(User $user): void
    {
        $user->currentAccessToken()?->delete();
    }
}
