<?php

namespace App\Http\Filters;

use App\Http\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class StockTransactionFilter extends Filter
{
    public function product_id(?int $value = null): Builder
    {
        if (!$value)
            return $this->builder;
        return $this->builder->where('product_id', $value);
    }

    public function operation(?string $value = null): Builder
    {
        if (!$value)
            return $this->builder;
        return $this->builder->where('operation', $value);
    }

    public function reason(?string $value = null): Builder
    {
        if (!$value)
            return $this->builder;
        return $this->builder->where('reason', 'like', "%$value%");
    }
}
