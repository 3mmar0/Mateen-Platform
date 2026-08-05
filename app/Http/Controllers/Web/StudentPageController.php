<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class StudentPageController extends Controller
{
    public function dashboard(): View
    {
        return view('pages.student.dashboard');
    }

    public function general(): View
    {
        return view('pages.student.general');
    }

    public function view(): View
    {
        return view('pages.student.view');
    }
}
