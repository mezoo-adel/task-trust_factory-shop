<?php

namespace App\Http\Filters;

use App\Http\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class OrderFilter extends Filter
{
    public function uuid(?string $value = null): Builder
    {
        if (!$value)
            return $this->builder;
        return $this->builder->where('uuid', 'like', "%$value%");
    }

    public function status(?string $value = null): Builder
    {
        if (!$value)
            return $this->builder;
        return $this->builder->where('status', $value);
    }

    public function search(?string $value = null): Builder
    {
        if (!$value)
            return $this->builder;
        return $this->builder->where(function ($query) use ($value) {
            $query
                ->where('uuid', 'like', "%$value%")
                ->orWhereHas('user', function ($userQuery) use ($value) {
                    $userQuery
                        ->where('name', 'like', "%$value%")
                        ->orWhere('email', 'like', "%$value%");
                });
        });
    }
}
