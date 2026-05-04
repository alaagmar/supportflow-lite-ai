<?php

declare(strict_types=1);

namespace App\Http\Requests\Staff;

use App\Http\Requests\Auth\LoginRequest;

class StaffLoginRequest extends LoginRequest
{
    // Role-prefixed request class keeps staff HTTP input separate from other portals.
}
