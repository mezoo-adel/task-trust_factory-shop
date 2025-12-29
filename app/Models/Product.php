<?php

namespace App\Models;

use App\Traits\Sluggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes, Sluggable;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'stock_quantity',
        'stock_threshold',
        'is_active',
    ];

    protected $appends = ['image_urls'];

    public function getRouteKeyName()
    {
        return 'slug';
    }

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'stock_quantity' => 'integer',
            'stock_threshold' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function uploads()
    {
        return $this->morphMany(Upload::class, 'uploadable')->orderBy('order');
    }

    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function stockTransactions()
    {
        return $this->hasMany(StockTransaction::class);
    }

    public function isLowStock(): bool
    {
        return $this->stock_quantity <= $this->stock_threshold;
    }

    public function isInStock(): bool
    {
        return $this->stock_quantity > 0;
    }


    public function getImageUrlsAttribute(): array
    {
        return $this->uploads->map(function ($upload) {
            return asset('storage/' . $upload->file_path);
        })->toArray();
    }

}
