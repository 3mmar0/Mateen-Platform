<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class StaffPageController extends Controller
{
    public function admin(): View
    {
        return view('pages.admin.dashboard');
    }

    public function supervisor(): View
    {
        return view('pages.supervisor.dashboard');
    }

    public function support(): View
    {
        return view('pages.support.dashboard');
    }

    public function myStudents(): View
    {
        return view('pages.staff.my-students');
    }

    public function messages(): View
    {
        return view('pages.messages');
    }

    public function stats(): View
    {
        return view('pages.stats');
    }
}
