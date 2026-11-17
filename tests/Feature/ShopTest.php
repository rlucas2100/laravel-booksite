<?php

namespace Tests\Feature;

use App\Enums\EntryType;
use App\Models\Category;
use App\Models\Entry;
use App\Models\Order;
use App\Models\User;
use App\Support\Money;
use App\Support\Sandbox;
use App\Support\Stripe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as StripeRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/** Prices, the basket, paying through Stripe Checkout and what a purchase unlocks. Stripe itself is faked. */
class ShopTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake(Entry::DISK);
        config(['services.stripe.secret' => 'sk_test_abc123', 'services.stripe.webhook_secret' => 'whsec_test']);
        Http::preventStrayRequests();
    }

    private function pricedBook(int $pence = 299, array $attributes = []): Entry
    {
        $book = $this->entry(EntryType::Book, $attributes + ['file_path' => 'files/book/'.uniqid().'.pdf', 'price' => $pence]);
        Storage::disk(Entry::DISK)->put($book->file_path, '%PDF-1.4');

        return $book;
    }

    /** Pretend to be Stripe: creating a session, and reporting on it afterwards. */
    private function fakeStripe(string $paymentStatus = 'paid'): void
    {
        Http::fake([
            'api.stripe.com/v1/checkout/sessions' => fn (StripeRequest $request) => Http::response([
                'id' => 'cs_test_1', 'url' => 'https://checkout.stripe.com/c/pay/cs_test_1',
            ]),
            'api.stripe.com/v1/checkout/sessions/*' => fn () => Http::response([
                'id' => 'cs_test_1',
                'payment_status' => $paymentStatus,
                'payment_intent' => 'pi_test_1',
                'client_reference_id' => Order::withoutGlobalScopes()->latest('id')->value('reference'),
            ]),
        ]);
    }

    public function test_prices_are_kept_in_pence_and_shown_in_pounds(): void
    {
        $this->assertSame(299, Money::pence('2.99'));
        $this->assertSame(1000, Money::pence(10));
        $this->assertNull(Money::pence(''));
        $this->assertNull(Money::pence('0'));
        $this->assertSame('£2.99', Money::pounds(299));
        $this->assertSame('£0.79', Money::pounds(79));

        $book = $this->pricedBook(499, ['title' => 'A Priced Book']);
        $free = $this->entry(EntryType::Book, ['title' => 'A Free Book']);

        $this->get('/books')->assertSee('£4.99');
        $this->get($book->url())->assertSee('£4.99')->assertSee('Add to basket');
        $this->get($free->url())->assertDontSee('Add to basket');
    }

    public function test_a_member_sets_a_price_when_uploading(): void
    {
        $category = Category::factory()->create();
        $form = ['type' => 'article', 'creator' => 'Me', 'category_id' => $category->id, 'description' => 'A description long enough.', 'body' => 'Text'];

        $this->actingAs($this->member());
        $this->post('/upload', $form + ['title' => 'Paid Post', 'price' => '1.50'])->assertSessionHasNoErrors();
        $this->post('/upload', $form + ['title' => 'Free Post', 'price' => ''])->assertSessionHasNoErrors();
        $this->post('/upload', $form + ['title' => 'Silly Post', 'price' => '-3'])->assertSessionHasErrors('price');
        $this->post('/upload', $form + ['title' => 'Dear Post', 'price' => '500'])->assertSessionHasErrors('price');

        $this->assertSame(150, Entry::query()->where('title', 'Paid Post')->value('price'));
        $this->assertNull(Entry::query()->where('title', 'Free Post')->value('price'));
    }

    public function test_the_basket_holds_priced_entries_for_guests_too(): void
    {
        $one = $this->pricedBook(299, ['title' => 'First Book']);
        $two = $this->pricedBook(199, ['title' => 'Second Book']);
        $free = $this->entry(EntryType::Book);

        $this->post(route('basket.add', $one))->assertRedirect();
        $this->post(route('basket.add', $two));
        $this->post(route('basket.add', $one));              // twice is still once
        $this->post(route('basket.add', $free))->assertNotFound();

        $this->get('/basket')->assertOk()->assertSee('First Book')->assertSee('Second Book')->assertSee('£4.98')->assertSee('Sign in to pay');

        $this->delete(route('basket.remove', $one));
        $this->get('/basket')->assertDontSee('First Book')->assertSee('£1.99');

        $this->post('/checkout')->assertRedirect('/login');
    }

    public function test_a_priced_file_is_locked_until_it_is_bought(): void
    {
        $book = $this->pricedBook();
        $audio = $this->entry(EntryType::Audio, ['file_path' => 'files/audio/a.mp3', 'price' => 99]);
        Storage::disk(Entry::DISK)->put('files/audio/a.mp3', 'mp3');

        // Guests are asked to sign in; members are sent back to the page to buy it.
        $this->get(route('media.file', $audio))->assertRedirect('/login');
        $this->actingAs($this->member())->get(route('media.file', $book))->assertRedirect($book->url());
        $this->get(route('media.file', $audio))->assertRedirect($audio->url());
        $this->get($audio->url())->assertDontSee('<audio', false);

        // The uploader always has their own file.
        $this->actingAs($book->user)->get(route('media.file', $book))->assertOk();
    }

    public function test_paying_through_stripe_unlocks_what_was_bought(): void
    {
        $this->fakeStripe();
        $member = $this->member();
        $book = $this->pricedBook(299, ['title' => 'Bought Book']);
        $other = $this->pricedBook(499, ['title' => 'Other Book']);

        $this->actingAs($member)->post(route('basket.add', $book));
        $this->post(route('basket.add', $other));

        // Checkout makes a pending order and sends the buyer to Stripe's own page.
        $this->post('/checkout')->assertRedirect('https://checkout.stripe.com/c/pay/cs_test_1');

        $order = Order::query()->firstOrFail();
        $this->assertSame([Order::PENDING, 798, 'cs_test_1'], [$order->status, $order->total, $order->stripe_session]);
        $this->assertFalse($member->fresh()->mayOpen($book));   // not paid yet, so not unlocked yet

        Http::assertSent(function (StripeRequest $request) use ($order) {
            return $request->url() === 'https://api.stripe.com/v1/checkout/sessions'
                && $request->hasHeader('Authorization', 'Bearer sk_test_abc123')
                && $request['mode'] === 'payment'
                && $request['client_reference_id'] === $order->reference
                && $request['line_items'][0]['price_data']['currency'] === 'gbp'
                && (int) $request['line_items'][0]['price_data']['unit_amount'] === 299
                && $request['line_items'][0]['price_data']['product_data']['name'] === 'Bought Book';
        });

        // Back from Stripe: the shop asks Stripe itself whether the session was paid.
        $this->get('/checkout/success?session_id=cs_test_1')->assertRedirect('/library');

        $this->assertTrue($order->fresh()->isPaid());
        $this->assertSame('pi_test_1', $order->fresh()->stripe_payment);

        $this->get('/library')->assertOk()->assertSee('Bought Book')->assertSee('Other Book')->assertSee($order->reference)->assertSee('£7.98');
        $this->get(route('media.file', $book))->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $this->get($book->url())->assertSee('Yours')->assertDontSee('Add to basket');
        $this->get('/basket')->assertSee('Nothing in here yet');

        // Somebody else still has to pay.
        $this->actingAs($this->member())->get(route('media.file', $book))->assertRedirect($book->url());
    }

    public function test_an_unpaid_or_someone_elses_session_unlocks_nothing(): void
    {
        $this->fakeStripe(paymentStatus: 'unpaid');
        $member = $this->member();
        $book = $this->pricedBook();

        $this->actingAs($member)->post(route('basket.add', $book));
        $this->post('/checkout');

        $this->get('/checkout/success?session_id=cs_test_1')->assertRedirect('/library');
        $this->assertFalse(Order::query()->firstOrFail()->isPaid());
        $this->get(route('media.file', $book))->assertRedirect($book->url());

        // A session id that isn't this member's order is not found.
        $this->get('/checkout/success?session_id=cs_test_made_up')->assertNotFound();
        $this->actingAs($this->member())->get('/checkout/success?session_id=cs_test_1')->assertNotFound();
    }

    public function test_the_webhook_marks_an_order_paid_only_when_stripe_signed_it(): void
    {
        $this->fakeStripe();
        $member = $this->member();
        $book = $this->pricedBook();

        $this->actingAs($member)->post(route('basket.add', $book));
        $this->post('/checkout');
        $order = Order::query()->firstOrFail();
        $this->app['auth']->forgetGuards();

        $payload = json_encode(['type' => 'checkout.session.completed', 'data' => ['object' => [
            'id' => 'cs_test_1', 'payment_status' => 'paid', 'payment_intent' => 'pi_hook', 'client_reference_id' => $order->reference,
        ]]]);
        $send = fn (string $signature) => $this->call('POST', '/stripe/webhook', [], [], [], ['HTTP_STRIPE_SIGNATURE' => $signature, 'CONTENT_TYPE' => 'application/json'], $payload);
        $sign = fn (int $time, string $secret) => 't='.$time.',v1='.hash_hmac('sha256', $time.'.'.$payload, $secret);

        $send('t='.time().',v1=forged')->assertStatus(400);
        $send($sign(time(), 'whsec_wrong'))->assertStatus(400);
        $send($sign(time() - 3600, 'whsec_test'))->assertStatus(400);     // an old message replayed
        $this->assertFalse($order->fresh()->isPaid());

        $send($sign(time(), 'whsec_test'))->assertOk();
        $this->assertTrue($order->fresh()->isPaid());
        $this->assertTrue($member->fresh()->mayOpen($book));

        $send($sign(time(), 'whsec_test'))->assertOk();                   // Stripe may send it twice: nothing changes
        $this->assertSame('pi_hook', $order->fresh()->stripe_payment);
    }

    public function test_the_demo_refuses_a_live_key_and_checkout_waits_for_keys(): void
    {
        $book = $this->pricedBook();
        $member = $this->member();
        $this->actingAs($member)->post(route('basket.add', $book));

        config(['services.stripe.secret' => null]);
        $this->assertFalse(Stripe::ready());
        $this->post('/checkout')->assertRedirect('/basket');

        config(['services.stripe.secret' => 'sk_live_realmoney', 'shop.demo' => true]);
        $this->assertFalse(Stripe::ready());
        $this->post('/checkout')->assertRedirect('/basket');

        $this->assertSame(0, Order::withoutGlobalScopes()->count());
        Http::assertNothingSent();
    }

    public function test_in_the_demo_a_purchase_belongs_to_the_browser_that_made_it(): void
    {
        $this->fakeStripe();
        $reader = $this->member();                        // the shared demo account
        $book = $this->pricedBook();
        config(['shop.demo' => true]);

        $alice = str_repeat('a', 32);
        $bob = str_repeat('b', 32);

        $this->actingAs($reader)->withCookie(Sandbox::COOKIE, $alice)->post(route('basket.add', $book));
        $this->withCookie(Sandbox::COOKIE, $alice)->post('/checkout');
        $this->withCookie(Sandbox::COOKIE, $alice)->get('/checkout/success?session_id=cs_test_1')->assertRedirect('/library');
        $this->withCookie(Sandbox::COOKIE, $alice)->get(route('media.file', $book))->assertOk();

        // The same account in another browser has not bought it.
        $this->flushSession();
        $this->actingAs(User::query()->findOrFail($reader->id))->withCookie(Sandbox::COOKIE, $bob)->get(route('media.file', $book))->assertRedirect($book->url());
    }

    public function test_admins_see_the_orders(): void
    {
        $this->actingAs($this->member())->get('/admin/orders')->assertForbidden();
        $this->actingAs($this->admin())->get('/admin/orders')->assertOk()->assertSee('Paid orders');
    }
}
