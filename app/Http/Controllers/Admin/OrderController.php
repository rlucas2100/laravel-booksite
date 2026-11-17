<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Every order, newest first, with the takings. */
class OrderController extends Controller
{
    public function __invoke(Request $request): View
    {
        abort_unless($request->user()->can('manage-entries'), 403);

        return view('admin.orders', [
            'orders' => Order::query()->with(['user', 'items'])->latest()->paginate(20),
            'paidCount' => Order::query()->where('status', Order::PAID)->count(),
            'takings' => (int) Order::query()->where('status', Order::PAID)->sum('total'),
        ]);
    }
}
