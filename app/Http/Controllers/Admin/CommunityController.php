<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Moderation: the newest reviews and comments, each with a delete button. */
class CommunityController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()->can('moderate-community'), 403);

        return view('admin.community', [
            'reviews' => Review::query()->with(['user', 'entry'])->latest()->paginate(10, pageName: 'reviews')->withQueryString(),
            'comments' => Comment::query()->with(['user', 'entry'])->latest()->paginate(10, pageName: 'comments')->withQueryString(),
        ]);
    }
}
