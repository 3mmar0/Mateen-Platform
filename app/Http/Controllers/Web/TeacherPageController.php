<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class TeacherPageController extends Controller
{
    public function tafseer(): View
    {
        return view('pages.teacher.tafseer');
    }

    public function fiqh(): View
    {
        return view('pages.teacher.fiqh');
    }

    public function aqeedah(): View
    {
        return view('pages.teacher.aqeedah');
    }

    public function hadeeth(): View
    {
        return view('pages.teacher.hadeeth');
    }

    public function quran1(): View
    {
        return view('pages.teacher.quran1');
    }

    public function quran2(): View
    {
        return view('pages.teacher.quran2');
    }

    public function ithraiyat(): View
    {
        return view('pages.teacher.ithraiyat');
    }

    public function library(): View
    {
        return view('pages.teacher.library');
    }

    public function profile(): View
    {
        return view('pages.teacher.profile');
    }

    public function schedule(): View
    {
        return view('pages.teacher.schedule');
    }

    public function students(): View
    {
        return view('pages.teacher.students');
    }
}
