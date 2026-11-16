<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Entry;
use App\Models\Review;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.dashboard', [
            // ['book' => 28, 'audio' => 5, ...]
            'counts' => Entry::query()->toBase()->selectRaw('type, count(*) as total')->groupBy('type')->pluck('total', 'type'),
            'members' => User::query()->visibleHere()->count(),
            'reviewCount' => Review::query()->count(),
            'commentCount' => Comment::query()->count(),
            'averageRating' => ($average = Review::query()->avg('rating')) === null ? null : (float) $average,
            // [5 => 40, 4 => 22, ... 0 => 1]
            'spread' => Review::query()->toBase()->selectRaw('rating, count(*) as total')->groupBy('rating')->pluck('total', 'rating'),
            'latestReviews' => Review::query()->with(['user', 'entry'])->latest()->limit(5)->get(),
            'latestEntries' => Entry::query()->with(['user', 'category'])->latest()->limit(5)->get(),
            'topRated' => Entry::query()->withRatings()->has('reviews', '>=', 2)->sorted('top')->limit(5)->get(),
        ]);
    }
}
