<?php

namespace App\Models;

use App\Enums\OrderStatusEnum;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasUuid;
    protected $fillable = [
        'user_id',
        'address_id',
        'uuid',
        'status',
        'subtotal',
        'tax',
        'shipping',
        'total',
        'stripe_payment_intent_id',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => OrderStatusEnum::class,
            'subtotal' => 'decimal:2',
            'tax' => 'decimal:2',
            'shipping' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function address()
    {
        return $this->belongsTo(Address::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function stockTransactions()
    {
        return $this->hasMany(StockTransaction::class);
    }

    /**
     * Check if order can be cancelled
     * Order can be cancelled if status is not 'cancelled' or 'delivered'
     */
    public function getIsCancellableAttribute(): bool
    {
        return !in_array($this->status, [
            OrderStatusEnum::CANCELLED,
            OrderStatusEnum::DELIVERED,
        ]);
    }

    /**
     * Cancel the order
     *
     * @return bool
     */
    public function cancel(): bool
    {
        if (!$this->is_cancellable) {
            return false;
        }

        $this->update(['status' => OrderStatusEnum::CANCELLED]);
        return true;
    }
}
