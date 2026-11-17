<?php

namespace App\Support;

use App\Models\Order;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * The few calls this shop makes to Stripe, over plain HTTPS (no SDK needed).
 *
 * The buyer pays on Stripe's own Checkout page, so no card number ever reaches
 * this site. We create a Checkout Session, send the buyer to it, and afterwards
 * ask Stripe whether that session was paid. Stripe also tells us itself, through
 * the webhook (see StripeWebhookController).
 *
 * In the public demo only TEST keys are accepted, so no real money can move.
 */
class Stripe
{
    private const API = 'https://api.stripe.com/v1';

    public static function secret(): ?string
    {
        return config('services.stripe.secret') ?: null;
    }

    /** Is there a usable key? In the demo a live key counts as "no". */
    public static function ready(): bool
    {
        $secret = static::secret();

        return $secret !== null && (! Sandbox::enabled() || static::isTestKey($secret));
    }

    public static function isTestKey(string $secret): bool
    {
        return str_starts_with($secret, 'sk_test_');
    }

    /** Create a Checkout Session for an order. Returns Stripe's session: ['id' => ..., 'url' => ...]. */
    public static function createCheckout(Order $order, string $successUrl, string $cancelUrl): array
    {
        $lines = [];

        foreach ($order->items as $item) {
            $lines[] = [
                'quantity' => 1,
                'price_data' => [
                    'currency' => $order->currency,
                    'unit_amount' => $item->price,
                    'product_data' => ['name' => $item->title],
                ],
            ];
        }

        return static::client()
            // The same order sent twice (a double click) gives one session, not two.
            ->withHeaders(['Idempotency-Key' => 'checkout-'.$order->reference])
            ->post(self::API.'/checkout/sessions', [
                'mode' => 'payment',
                'line_items' => $lines,
                'client_reference_id' => $order->reference,
                'metadata' => ['order' => $order->reference],
                'success_url' => $successUrl.'?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => $cancelUrl,
            ])
            ->throw()
            ->json();
    }

    /** Ask Stripe about a session (never trust what the browser says about it). */
    public static function session(string $id): array
    {
        return static::client()->get(self::API.'/checkout/sessions/'.rawurlencode($id))->throw()->json();
    }

    /**
     * Check that a webhook really came from Stripe: its signature is an HMAC of
     * "timestamp.body" made with the endpoint's secret, and it must be recent.
     */
    public static function signatureIsValid(string $payload, ?string $header, string $secret, int $tolerance = 300): bool
    {
        $timestamp = null;
        $signatures = [];

        foreach (explode(',', (string) $header) as $part) {
            [$key, $value] = array_pad(explode('=', trim($part), 2), 2, '');

            if ($key === 't') {
                $timestamp = $value;
            } elseif ($key === 'v1') {
                $signatures[] = $value;
            }
        }

        if (! ctype_digit((string) $timestamp) || abs(time() - (int) $timestamp) > $tolerance) {
            return false;
        }

        $expected = hash_hmac('sha256', $timestamp.'.'.$payload, $secret);

        foreach ($signatures as $signature) {
            if (hash_equals($expected, $signature)) {
                return true;
            }
        }

        return false;
    }

    private static function client(): PendingRequest
    {
        if (! static::ready()) {
            throw new RuntimeException('Stripe is not set up, or a live key was given to the demo.');
        }

        return Http::asForm()->withToken(static::secret())->timeout(20);
    }
}
