<?php

namespace App\Http\Controllers\Auth;

use Inertia\Inertia;
use App\Http\Controllers\Controller;

class InertiaAuthController extends Controller
{
    public function login()
    {
        return Inertia::render('Auth/Login');
    }

    public function register()
    {
        return Inertia::render('Auth/Register');
    }
}
