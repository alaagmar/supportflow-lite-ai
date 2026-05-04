<?php

declare(strict_types=1);

namespace App\Http\Requests\Owner;

use App\Http\Requests\Auth\LoginRequest;

class OwnerLoginRequest extends LoginRequest
{
    // Role-prefixed request class keeps owner HTTP input separate from other portals.
}
