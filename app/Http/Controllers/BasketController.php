<?php

namespace App\Http\Controllers;

use App\Models\Entry;
use App\Support\Basket;
use App\Support\Stripe;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** The shopping basket. Anyone can fill one; you sign in when you pay. */
class BasketController extends Controller
{
    public function show(): View
    {
        $entries = Basket::entries();

        return view('shop.basket', [
            'entries' => $entries,
            'total' => Basket::total($entries),
            'paymentsReady' => Stripe::ready(),
        ]);
    }

    public function add(Request $request, Entry $entry): RedirectResponse
    {
        abort_if($entry->isFree(), 404);

        if ($request->user()?->mayOpen($entry)) {
            return back()->with('status', 'That one is already yours.');
        }

        Basket::add($entry);

        return back()->with('status', '"'.$entry->title.'" is in your basket.');
    }

    public function remove(Entry $entry): RedirectResponse
    {
        Basket::remove($entry);

        return back()->with('status', 'Taken out of your basket.');
    }
}
