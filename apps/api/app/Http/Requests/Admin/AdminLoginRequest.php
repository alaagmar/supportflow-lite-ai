<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Http\Requests\Auth\LoginRequest;

class AdminLoginRequest extends LoginRequest
{
    // Role-prefixed request class keeps admin HTTP input separate from other portals.
}
