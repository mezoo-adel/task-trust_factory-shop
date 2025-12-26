<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    protected $fillable = [
        'cart_id',
        'product_id',
        'quantity',
        'discount',
    ];

    protected $with = ['product'];
    protected $appends = ['total'];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'discount' => 'decimal:2',
        ];
    }

    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    protected function getTotalAttribute()
    {
        return $this->product->price * $this->quantity - $this->discount;
    }
}
