<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Upload extends Model
{
    protected $fillable = [
        'uploadable_id',
        'uploadable_type',
        'file_name',
        'file_path',
        'file_type',
        'file_size',
        'mime_type',
        'order',
    ];

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
            'order' => 'integer',
        ];
    }

    public function uploadable()
    {
        return $this->morphTo();
    }
}
