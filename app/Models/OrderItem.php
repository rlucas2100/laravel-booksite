<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** One line of an order: which entry, and what it cost at the time. */
class OrderItem extends Model
{
    protected $fillable = ['entry_id', 'title', 'price'];

    protected function casts(): array
    {
        return ['price' => 'integer'];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function entry(): BelongsTo
    {
        return $this->belongsTo(Entry::class);
    }
}
