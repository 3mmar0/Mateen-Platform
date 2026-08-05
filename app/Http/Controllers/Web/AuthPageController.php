<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class AuthPageController extends Controller
{
    public function login(): View
    {
        return view('pages.login');
    }

    public function onboarding(): View
    {
        return view('pages.onboarding');
    }
}
