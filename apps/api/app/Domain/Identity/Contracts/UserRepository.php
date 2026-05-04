<?php

declare(strict_types=1);

namespace App\Domain\Identity\Contracts;

use App\Models\User;

interface UserRepository
{
    public function create(string $name, string $email, string $password): User;

    public function findByEmail(string $email): ?User;
}
