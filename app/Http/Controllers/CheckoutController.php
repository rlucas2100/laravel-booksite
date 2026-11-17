<?php

namespace App\Http\Controllers;

use App\Models\Entry;
use App\Models\Order;
use App\Support\Basket;
use App\Support\Stripe;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

/**
 * Paying. The basket becomes a "pending" order, the buyer is sent to Stripe's
 * Checkout page, and the order only becomes "paid" once Stripe itself says so.
 */
class CheckoutController extends Controller
{
    /** Start the payment: make the order, then go to Stripe. */
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        // Only what is priced and not already bought.
        $entries = Basket::entries()->reject(fn (Entry $entry) => $user->mayOpen($entry))->values();

        if ($entries->isEmpty()) {
            Basket::clear();

            return redirect()->route('basket.show')->with('status', 'There is nothing to pay for in your basket.');
        }

        if (! Stripe::ready()) {
            return redirect()->route('basket.show')->with('status', 'Payments are not switched on for this site yet.');
        }

        $order = DB::transaction(function () use ($user, $entries) {
            $order = new Order;
            $order->user_id = $user->id;
            $order->reference = 'CH-'.Str::upper(Str::random(12));
            $order->total = Basket::total($entries);
            $order->currency = 'gbp';
            $order->status = Order::PENDING;
            $order->save();

            // The title and price are copied, so the receipt stays true if the entry changes later.
            $order->items()->createMany($entries->map(fn (Entry $entry) => [
                'entry_id' => $entry->id,
                'title' => $entry->title,
                'price' => $entry->price,
            ])->all());

            return $order->load('items');
        });

        try {
            $session = Stripe::createCheckout($order, route('checkout.success'), route('basket.show'));
        } catch (Throwable $problem) {
            report($problem);
            $order->delete();

            return redirect()->route('basket.show')->with('status', 'The payment page could not be opened. Nothing was charged. Please try again.');
        }

        $order->forceFill(['stripe_session' => $session['id']])->save();

        return redirect()->away($session['url']);
    }

    /** Stripe sends the buyer back here. We ask Stripe whether the session was really paid. */
    public function success(Request $request): RedirectResponse
    {
        $order = Order::query()
            ->where('user_id', $request->user()->id)
            ->where('stripe_session', (string) $request->query('session_id'))
            ->firstOrFail();

        if (! $order->isPaid()) {
            try {
                $session = Stripe::session($order->stripe_session);
            } catch (Throwable $problem) {
                report($problem);
                $session = [];
            }

            if (($session['payment_status'] ?? null) === 'paid' && ($session['client_reference_id'] ?? null) === $order->reference) {
                $order->markPaid($session['payment_intent'] ?? null);
            }
        }

        if (! $order->isPaid()) {
            return redirect()->route('library')->with('status', 'We are still waiting for Stripe to confirm that payment. Refresh this page in a moment.');
        }

        Basket::clear();

        return redirect()->route('library')->with('status', 'Paid! Your order '.$order->reference.' is in your library.');
    }

    /** "My library": everything this member has bought, and their orders. */
    public function library(Request $request): View
    {
        $orders = $request->user()->orders()->where('status', Order::PAID)->with('items')->latest('paid_at')->get();

        $entries = Entry::query()->withRatings()->with('category')
            ->whereKey($request->user()->purchasedIds())->orderBy('title')->get();

        return view('shop.library', ['orders' => $orders, 'entries' => $entries]);
    }
}
