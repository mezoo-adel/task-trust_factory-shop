<?php

namespace App\Models;

use App\Traits\Filterable;
use Illuminate\Database\Eloquent\Model;

class StockTransaction extends Model
{
    use Filterable;
    protected $fillable = [
        'product_id',
        'order_id',
        'operation',
        'quantity',
        'previous_stock',
        'new_stock',
        'reason',
        'performed_by',
        'is_reserved',
        'is_returned',
        'is_damaged',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'previous_stock' => 'integer',
            'new_stock' => 'integer',
            'is_reserved' => 'boolean',
            'is_returned' => 'boolean',
            'is_damaged' => 'boolean',
        ];
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function performedBy()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}
