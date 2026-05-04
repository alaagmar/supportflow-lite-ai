<?php

declare(strict_types=1);

namespace App\Domain\Identity;

use App\Models\WorkspaceMember;
use InvalidArgumentException;

final class Portal
{
    public const OWNER = 'owner';

    public const ADMIN = 'admin';

    public const STAFF = 'staff';

    /**
     * @return list<string>
     */
    public static function rolesFor(string $portal): array
    {
        return match ($portal) {
            self::OWNER => [WorkspaceMember::ROLE_OWNER],
            self::ADMIN => [WorkspaceMember::ROLE_OWNER, WorkspaceMember::ROLE_ADMIN],
            self::STAFF => [WorkspaceMember::ROLE_OWNER, WorkspaceMember::ROLE_ADMIN, WorkspaceMember::ROLE_AGENT, WorkspaceMember::ROLE_VIEWER],
            default => throw new InvalidArgumentException("Unsupported portal [{$portal}]."),
        };
    }
}
