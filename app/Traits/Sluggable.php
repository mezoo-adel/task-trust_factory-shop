<?php
namespace App\Traits;

use Illuminate\Support\Str;

trait Sluggable
{
    protected static function bootSluggable()
    {
        static::creating(function ($model) {
            if ($model->slug) {
                return;
            }

            $sourceAttribute = $model->slug_source ?? 'name';
            $value = $model->getAttribute($sourceAttribute);
            $slug = Str::slug($value);

            $exists = $model::withoutGlobalScopes()->where('slug', $slug)->exists();
            if ($exists) {
                $slug .= '-' . Str::random(6);
            }

            $model->slug = strtolower($slug);
        });
    }
}
