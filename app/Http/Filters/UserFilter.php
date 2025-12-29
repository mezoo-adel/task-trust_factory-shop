<?php

namespace App\Http\Filters;

use App\Http\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class UserFilter extends Filter
{
    public function name(?string $value = null): Builder
    {
        if (!$value)
            return $this->builder;
        return $this->builder->where('name', 'like', "%$value%");
    }

    public function email(?string $value = null): Builder
    {
        if (!$value)
            return $this->builder;
        return $this->builder->where('email', 'like', "%$value%");
    }

    public function search(?string $value = null): Builder
    {
        if (!$value)
            return $this->builder;
        return $this->builder->where(function ($query) use ($value) {
            $query
                ->where('email', 'like', "%$value%")
                ->orWhere('name', 'like', "%$value%");
        });
    }

    public function order(?string $value = null): Builder
    {
        if (!$value)
            return $this->builder;
        $userid = auth()->id();
        $this->builder->where('id', '!=', $userid);
        return $this->builder->orderBy($this->sort, $value);
    }
}
