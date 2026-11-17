<?php

namespace App\Models;

use App\Models\Concerns\Sandboxed;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * One trip through the checkout. It starts "pending" and becomes "paid" when
 * Stripe confirms the payment. In the public demo an order belongs to the
 * browser that made it, like everything else a visitor creates.
 */
class Order extends Model
{
    use Sandboxed;

    public const PENDING = 'pending';

    public const PAID = 'paid';

    protected function casts(): array
    {
        return [
            'total' => 'integer',
            'paid_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function isPaid(): bool
    {
        return $this->status === self::PAID;
    }

    /** Called when Stripe says the money has arrived. Safe to call twice. */
    public function markPaid(?string $payment = null): void
    {
        if ($this->isPaid()) {
            return;
        }

        $this->forceFill(['status' => self::PAID, 'paid_at' => now(), 'stripe_payment' => $payment])->save();
    }
}
