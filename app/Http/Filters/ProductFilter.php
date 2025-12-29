<?php

namespace App\Http\Filters;

use App\Http\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class ProductFilter extends Filter
{
    public function name(?string $value = null): Builder
    {
        if (!$value)
            return $this->builder;
        return $this->builder->where('name', 'like', "%$value%");
    }

    public function stock_filter(?string $value = null): Builder
    {
        if ($value == 'low') {
            $this->builder->whereRaw('stock_quantity <= stock_threshold');
        } elseif ($value == 'out') {
            $this->builder->where('stock_quantity', 0);
        }

        return $this->builder;
    }

    public function description(?string $value = null): Builder
    {
        if (!$value)
            return $this->builder;
        return $this->builder->where('description', 'like', "%$value%");
    }

    public function search(?string $value = null): Builder
    {
        if (!$value)
            return $this->builder;
        return $this->builder->where(function ($query) use ($value) {
            $query
                ->where('name', 'like', "%$value%")
                ->orWhere('description', 'like', "%$value%");
        });
    }
}
