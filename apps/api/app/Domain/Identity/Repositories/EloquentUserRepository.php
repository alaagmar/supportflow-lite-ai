<?php

declare(strict_types=1);

namespace App\Domain\Identity\Repositories;

use App\Domain\Identity\Contracts\UserRepository;
use App\Models\User;
use Illuminate\Support\Str;

final class EloquentUserRepository implements UserRepository
{
    public function create(string $name, string $email, string $password): User
    {
        /** @var User $user */
        $user = User::query()->create([
            'name' => $name,
            'email' => Str::lower($email),
            'password' => $password,
        ]);

        return $user;
    }

    public function findByEmail(string $email): ?User
    {
        /** @var User|null $user */
        $user = User::query()
            ->where('email', Str::lower($email))
            ->first();

        return $user;
    }
}
