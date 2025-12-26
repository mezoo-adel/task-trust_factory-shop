<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Visitor extends Model
{
    protected $fillable = [
        'fingerprint',
    ];

    public function cart()
    {
        return $this->hasOne(Cart::class);
    }
}
