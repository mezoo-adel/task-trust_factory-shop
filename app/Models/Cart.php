<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    protected $fillable = [
        'user_id',
        'visitor_id',
        'discount',
        'tax',
    ];

    protected $with = ['items'];
    protected $appends = ['subtotal', 'total'];

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

    protected function casts(): array
    {
        return [
            'discount' => 'decimal:2',
            'tax' => 'decimal:2',
        ];
    }

    protected function getSubTotalAttribute()
    {
        return $this->items->sum('total');
    }

    protected function getTotalAttribute()
    {
        return $this->sub_total - $this->discount + $this->tax;
    }

    public function itemCount(): int
    {
        return $this->items->sum('quantity');
    }
}
