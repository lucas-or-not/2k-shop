<?php

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;

final class GetCurrentUserAction
{
    public function execute(): ?User
    {
        return Auth::user();
    }
}

