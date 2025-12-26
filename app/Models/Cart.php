<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    protected $fillable = [
        'user_id',
        'visitor_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function visitor()
    {
        return $this->belongsTo(Visitor::class);
    }

    public function items()
    {
        return $this->hasMany(CartItem::class);
    }

    protected function subtotal(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->items->sum(function ($item) {
                return $item->product->price * $item->quantity;
            })
        );
    }

    protected function total(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->subtotal
        );
    }

    public function itemCount(): int
    {
        return $this->items->sum('quantity');
    }
}
