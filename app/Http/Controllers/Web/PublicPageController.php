<?php

namespace App\Http\Controllers\Web;

use App\Enums\NewsStatus;
use App\Http\Controllers\Controller;
use App\Models\LibraryItem;
use App\Models\NewsItem;
use App\Models\ScheduleEntry;
use App\Models\Subject;
use Illuminate\View\View;

class PublicPageController extends Controller
{
    public function home(): View
    {
        return view('pages.home', $this->catalog());
    }

    public function about(): View
    {
        return view('pages.about');
    }

    public function courses(): View
    {
        return view('pages.courses', [
            'subjects' => Subject::query()->orderBy('sort_order')->get(),
        ]);
    }

    public function library(): View
    {
        return view('pages.library', [
            'libraryItems' => LibraryItem::query()->orderBy('sort_order')->get(),
        ]);
    }

    public function news(): View
    {
        return view('pages.news', [
            'newsItems' => NewsItem::query()
                ->where('status', NewsStatus::Published)
                ->latest('published_at')
                ->get(),
        ]);
    }

    public function schedule(): View
    {
        return view('pages.schedule', [
            'schedules' => ScheduleEntry::query()->orderBy('starts_at')->get(),
        ]);
    }

    /** @return array<string, mixed> */
    private function catalog(): array
    {
        $icons = [
            'tafsir' => '📖',
            'tafseer' => '📖',
            'fiqh' => '⚖️',
            'aqeedah' => '🕌',
            'hadeeth' => '📜',
            'maqraah' => '🌿',
            'quran1' => '🌿',
            'quran2' => '🌿',
        ];

        return [
            'subjects' => Subject::query()->orderBy('sort_order')->get()->map(function (Subject $s) use ($icons) {
                $s->setAttribute('icon', $icons[$s->slug] ?? '📚');

                return $s;
            }),
            'newsItems' => NewsItem::query()
                ->where('status', NewsStatus::Published)
                ->latest('published_at')
                ->limit(6)
                ->get(),
            'schedules' => ScheduleEntry::query()->orderBy('starts_at')->limit(8)->get(),
        ];
    }
}
