<?php

namespace App\Actions\Auth;

use Illuminate\Support\Facades\Auth;

final class LogoutAction
{
    public function execute(): bool
    {
        $user = Auth::user();

        if ($user) {
            Auth::guard('web')->logout();

            request()->session()->invalidate();
            request()->session()->regenerateToken();
        }

        return true;
    }
}

