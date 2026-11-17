<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Support\Sandbox;
use App\Support\Stripe;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Stripe calls this address itself when a payment completes, so an order is
 * marked paid even if the buyer closes the tab before coming back to the shop.
 * Only messages signed with our endpoint's secret are believed.
 */
class StripeWebhookController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $secret = config('services.stripe.webhook_secret');

        abort_unless($secret, 404);
        abort_unless(Stripe::signatureIsValid($request->getContent(), $request->header('Stripe-Signature'), $secret), 400, 'Bad signature.');

        $event = json_decode($request->getContent(), true) ?: [];
        $session = $event['data']['object'] ?? [];

        if (in_array($event['type'] ?? '', ['checkout.session.completed', 'checkout.session.async_payment_succeeded'], true)
            && ($session['payment_status'] ?? null) === 'paid') {
            // Stripe has no visitor cookie, so the demo's "only your own browser" filter is set aside here.
            Sandbox::pause(function () use ($session) {
                Order::query()->where('reference', (string) ($session['client_reference_id'] ?? ''))->first()
                    ?->markPaid($session['payment_intent'] ?? null);
            });
        }

        return response('ok');
    }
}
