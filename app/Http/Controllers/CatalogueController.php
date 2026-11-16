<?php

namespace App\Http\Controllers;

use App\Enums\EntryType;
use App\Models\Category;
use App\Models\Entry;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * The public side of the shop: browsing, searching and reading one entry.
 * One controller serves every type, because they are all rows in one table.
 */
class CatalogueController extends Controller
{
    /** Everything, with search and filters. */
    public function index(Request $request): View
    {
        return $this->listing($request);
    }

    /** One shelf: /books, /videos, /audio or /blog. */
    public function shelf(Request $request, EntryType $shelf): View
    {
        return $this->listing($request, type: $shelf);
    }

    /** Everything in one category. Guests can browse these too. */
    public function category(Request $request, Category $category): View
    {
        return $this->listing($request, category: $category);
    }

    private function listing(Request $request, ?EntryType $type = null, ?Category $category = null): View
    {
        $text = fn (string $key): string => is_string($request->query($key)) ? trim($request->query($key)) : '';

        $search = mb_substr($text('q'), 0, 80);
        $sort = array_key_exists($text('sort'), Entry::SORTS) ? $text('sort') : 'new';

        // The page fixes the type or the category; whatever it doesn't fix can come from the filters.
        $chosenType = $type ?? EntryType::tryFrom($text('type'));
        $chosenCategory = $category ?? ($text('category') !== '' ? Category::query()->where('slug', $text('category'))->first() : null);

        $entries = Entry::query()
            ->published()
            ->withRatings()
            ->with('category')
            ->when($chosenType, fn ($query) => $query->ofType($chosenType))
            ->when($chosenCategory, fn ($query) => $query->whereBelongsTo($chosenCategory))
            ->when($search !== '', fn ($query) => $query->search($search))
            ->sorted($sort)
            ->paginate(config('shop.per_page'))
            ->withQueryString();

        return view('catalogue.index', [
            'entries' => $entries,
            'type' => $type,
            'category' => $category,
            'chosenType' => $chosenType,
            'chosenCategory' => $chosenCategory,
            'categories' => Category::query()->orderBy('name')->get(),
            'search' => $search,
            'sort' => $sort,
        ]);
    }

    /** One entry: /books/dracula. */
    public function show(EntryType $shelf, Entry $entry): View|RedirectResponse
    {
        abort_unless($entry->published_at?->isPast(), 404);

        // Found on the wrong shelf (/videos/dracula): send it to the right address.
        if ($entry->type !== $shelf) {
            return redirect()->to($entry->url(), 301);
        }

        $entry->load(['category', 'user'])->loadAvg('reviews', 'rating')->loadCount(['reviews', 'comments']);

        $reviews = $entry->reviews()->with('user')->latest()->limit(100)->get();

        // How many reviews gave each number of stars, 5 down to 0.
        $spread = [];
        foreach (range(Review::MAX, Review::MIN) as $stars) {
            $spread[$stars] = $reviews->where('rating', $stars)->count();
        }

        $comments = $entry->comments()->whereNull('parent_id')
            ->with(['user', 'replies.user'])->latest()->limit(100)->get();

        $related = Entry::query()->published()->withRatings()->with('category')
            ->where('category_id', $entry->category_id)->whereKeyNot($entry->id)
            ->orderByRaw('type = ? desc', [$entry->type->value])->sorted('top')->limit(4)->get();

        return view('catalogue.show', [
            'entry' => $entry,
            'reviews' => $reviews,
            'spread' => $spread,
            'myReview' => auth()->check() ? $reviews->firstWhere('user_id', auth()->id()) : null,
            'comments' => $comments,
            'related' => $related,
        ]);
    }
}
