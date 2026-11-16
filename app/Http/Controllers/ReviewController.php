<?php

namespace App\Http\Controllers;

use App\Models\Entry;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    /** Post a review, or replace the one you already wrote for this entry. */
    public function store(Request $request, Entry $entry): RedirectResponse
    {
        $this->authorize('create', [Review::class, $entry]);

        $data = $request->validate([
            'rating' => ['required', 'integer', 'between:'.Review::MIN.','.Review::MAX],
            'title' => ['nullable', 'string', 'max:100'],
            'body' => ['nullable', 'string', 'max:3000'],
        ], [
            'rating.required' => 'Pick a number of stars, from 0 to 5.',
            'rating.between' => 'Stars go from 0 to 5.',
        ]);

        // One review per member per entry.
        $review = $entry->reviews()->where('user_id', $request->user()->id)->first() ?? new Review;
        $existed = $review->exists;

        $review->fill($data);
        $review->entry_id = $entry->id;
        $review->user_id = $request->user()->id;
        $review->save();

        return redirect()->to($entry->url().'#reviews')
            ->with('status', $existed ? 'Your review was updated.' : 'Thanks! Your review is up.');
    }

    public function destroy(Review $review): RedirectResponse
    {
        $this->authorize('delete', $review);

        $review->delete();

        return back()->with('status', 'The review was removed.');
    }
}
