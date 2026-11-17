@php
    use App\Support\Money;
@endphp
<x-layout title="My library">
    <div class="shell py-10">
        <p class="eyebrow text-soft">Bought and paid for</p>
        <h1 class="mt-1 text-4xl font-black sm:text-5xl">My library</h1>

        @if ($entries->isEmpty())
            <div class="mx-auto mt-12 max-w-md text-center">
                <x-pip mood="3" class="mx-auto h-32 w-32"/>
                <h2 class="mt-4 text-3xl font-black">Nothing bought yet</h2>
                <p class="mt-2 text-soft">What you buy appears here, ready to download or play whenever you like. Everything free is on the shelves already.</p>
                <a href="{{ route('browse') }}" class="btn btn-lg btn-primary mt-6">Browse the shelves <x-icon name="arrow-right"/></a>
            </div>
        @else
            <div class="mt-9 grid grid-cols-2 gap-x-5 gap-y-9 sm:grid-cols-3 lg:grid-cols-4">
                @foreach ($entries as $entry)
                    <x-entry-card :entry="$entry" stage show-type/>
                @endforeach
            </div>
        @endif

        @if ($orders->isNotEmpty())
            <h2 class="mt-16 text-3xl font-black">Your orders</h2>
            <div class="card mt-5 overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr><th>Order</th><th>Date</th><th>Items</th><th class="text-right">Total</th></tr>
                    </thead>
                    <tbody>
                        @foreach ($orders as $order)
                            <tr>
                                <td class="font-mono text-sm font-bold whitespace-nowrap">{{ $order->reference }}</td>
                                <td class="whitespace-nowrap">{{ $order->paid_at->format('j M Y, H:i') }}</td>
                                <td>{{ $order->items->pluck('title')->join(', ') }}</td>
                                <td class="text-right font-bold tabular-nums">{{ Money::pounds($order->total) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</x-layout>
