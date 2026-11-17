@php
    use App\Support\Money;
@endphp
<x-admin title="Orders">
    <div class="grid grid-cols-2 gap-4 sm:max-w-md">
        <div class="card bg-mint p-4 text-night">
            <span class="pow text-4xl">{{ Money::pounds($takings) }}</span>
            <span class="mt-1 block font-bold">Taken (test money)</span>
        </div>
        <div class="card p-4">
            <span class="pow text-4xl">{{ $paidCount }}</span>
            <span class="mt-1 block font-bold">Paid orders</span>
        </div>
    </div>

    <div class="card mt-6 overflow-x-auto">
        <table class="table">
            <thead>
                <tr><th>Order</th><th>Member</th><th>Items</th><th>Status</th><th>When</th><th class="text-right">Total</th></tr>
            </thead>
            <tbody>
                @forelse ($orders as $order)
                    <tr>
                        <td class="font-mono text-sm font-bold whitespace-nowrap">{{ $order->reference }}</td>
                        <td>
                            {{ $order->user->name }}
                            @if ($order->isSandboxed()) <span class="chip bg-sun text-night">yours</span> @endif
                        </td>
                        <td>{{ $order->items->pluck('title')->join(', ') }}</td>
                        <td><span class="chip {{ $order->isPaid() ? 'bg-mint text-night' : '' }}">{{ $order->isPaid() ? 'Paid' : 'Not paid' }}</span></td>
                        <td class="text-sm whitespace-nowrap text-soft">{{ ($order->paid_at ?? $order->created_at)->diffForHumans() }}</td>
                        <td class="text-right font-bold tabular-nums">{{ Money::pounds($order->total) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="py-8 text-center text-soft">No orders yet. In the public demo you only see the ones made in this browser.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $orders->links() }}
</x-admin>
