@php
    use App\Support\Money;
    use App\Support\Sandbox;
@endphp
<x-layout title="Your basket">
    <div class="shell max-w-4xl py-10">
        <p class="eyebrow text-soft"><x-icon name="basket" size="h-4 w-4"/> Checkout</p>
        <h1 class="mt-1 text-4xl font-black sm:text-5xl">Your basket</h1>

        @if ($entries->isEmpty())
            <div class="mx-auto mt-12 max-w-md text-center">
                <x-pip mood="2" class="mx-auto h-32 w-32"/>
                <h2 class="mt-4 text-3xl font-black">Nothing in here yet</h2>
                <p class="mt-2 text-soft">Anything with a price has an “Add to basket” button on its page.</p>
                <a href="{{ route('browse') }}" class="btn btn-lg btn-primary mt-6">Browse the shelves <x-icon name="arrow-right"/></a>
            </div>
        @else
            <div class="mt-8 grid gap-8 lg:grid-cols-[1fr_20rem]">
                <ul class="space-y-4">
                    @foreach ($entries as $entry)
                        <li class="card tone-{{ $entry->type->colour() }} flex items-center gap-4 p-3 pr-4">
                            <x-cover :entry="$entry" class="shrink-0 shadow-pop-sm {{ $entry->type->coverShape() === 'wide' ? 'w-28' : 'w-16' }}"/>
                            <div class="min-w-0 flex-1">
                                <p class="eyebrow text-soft"><span class="dot-tone"></span>{{ $entry->type->label() }}</p>
                                <a href="{{ $entry->url() }}" class="font-display block truncate text-lg leading-snug font-bold hover:underline">{{ $entry->title }}</a>
                                <p class="truncate text-sm text-soft">{{ $entry->creator }}</p>
                            </div>
                            <p class="font-display text-xl font-black tabular-nums">{{ $entry->priceLabel() }}</p>
                            <form method="POST" action="{{ route('basket.remove', $entry) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-plain h-9 w-9 p-0!" aria-label="Take {{ $entry->title }} out of the basket"><x-icon name="close" size="h-4 w-4"/></button>
                            </form>
                        </li>
                    @endforeach
                </ul>

                <aside class="card h-fit p-5">
                    <h2 class="text-2xl font-black">Summary</h2>
                    <dl class="mt-4 space-y-2">
                        <div class="flex justify-between"><dt>{{ $entries->count() }} {{ Str::plural('item', $entries->count()) }}</dt><dd class="tabular-nums">{{ Money::pounds($total) }}</dd></div>
                        <div class="flex justify-between border-t-2 border-line pt-2 text-xl font-black"><dt>Total</dt><dd class="tabular-nums">{{ Money::pounds($total) }}</dd></div>
                    </dl>

                    @auth
                        <form method="POST" action="{{ route('checkout.store') }}" class="mt-5">
                            @csrf
                            <button type="submit" class="btn btn-lg btn-primary w-full" @disabled(! $paymentsReady)><x-icon name="lock"/> Pay {{ Money::pounds($total) }}</button>
                        </form>
                        @unless ($paymentsReady)
                            <p class="hint">Payments aren’t switched on for this site yet.</p>
                        @endunless
                    @else
                        <a href="{{ route('login') }}" class="btn btn-lg btn-primary mt-5 w-full">Sign in to pay</a>
                        <p class="hint">Your basket will still be here.</p>
                    @endauth

                    <p class="mt-4 flex items-center gap-2 text-xs text-soft"><x-icon name="shield" size="h-4 w-4"/> You pay on Stripe’s secure page. Card details never touch this site.</p>
                </aside>
            </div>

            @if (Sandbox::enabled())
                <div class="card mt-8 bg-sun p-5 text-night">
                    <p class="pow text-2xl">No real money here</p>
                    <p class="mt-1 font-medium">This shop is a demo and Stripe is in test mode. On the payment page choose Card and use the test card
                        <b class="rounded-md border-2 border-night bg-cream px-2 py-0.5 font-mono whitespace-nowrap">4242 4242 4242 4242</b>,
                        any date in the future, any three digits and any email address.</p>
                </div>
            @endif
        @endif
    </div>
</x-layout>
