<?php

namespace App\Http\Controllers;

use App\Enums\EntryType;
use App\Models\Category;
use App\Models\Entry;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $shelf = fn (): Builder => Entry::query()->published()->withRatings()->with('category');

        return view('home', [
            'featured' => $shelf()->ofType(EntryType::Book)->where('featured', true)->sorted('new')->limit(4)->get(),
            'newBooks' => $shelf()->ofType(EntryType::Book)->sorted('new')->limit(6)->get(),
            'favourites' => $shelf()->has('reviews', '>=', 2)->sorted('top')->limit(6)->get(),
            'audio' => $shelf()->ofType(EntryType::Audio)->sorted('new')->limit(4)->get(),
            'videos' => $shelf()->ofType(EntryType::Video)->sorted('new')->limit(3)->get(),
            'articles' => $shelf()->ofType(EntryType::Article)->sorted('new')->limit(3)->get(),
            'categories' => Category::query()
                ->withCount(['entries' => fn (Builder $entries) => $entries->published()])
                ->orderBy('name')->get(),
            // How many entries of each type: ['book' => 28, 'audio' => 5, ...]
            'counts' => Entry::query()->published()->toBase()
                ->selectRaw('type, count(*) as total')->groupBy('type')->pluck('total', 'type'),
        ]);
    }
}
