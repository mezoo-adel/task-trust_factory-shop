<?php

namespace App\Http\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

abstract class Filter
{
    /**
     * The request instance.
     *
     * @var \Illuminate\Http\Request
     */
    protected $request;

    /**
     * The builder instance.
     *
     * @var \Illuminate\Database\Eloquent\Builder
     */
    protected $builder;

    /*
     * Use this sort to sort a column
     */
    protected $sort = 'id';

    /**
     * Initialize a new filter instance.
     *
     * @param \Illuminate\Http\Request $request
     * @return void
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function sort(?string $value = null): self
    {
        if (!$value)
            return $this;
        $this->sort = $value;
        return $this;
    }

    public function order(?string $value = null): Builder
    {
        if (!$value)
            return $this->builder;
        return $this->builder->orderBy($this->sort, $value);
    }

    /**
     * Apply the filters on the builder.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function apply(Builder $builder): Builder
    {
        $this->builder = $builder;

        foreach ($this->request->all() as $name => $value) {
            if (method_exists($this, $name)) {
                call_user_func_array([$this, $name], array_filter([$value]));
            }
        }

        return $this->builder;
    }

    public function getRequest()
    {
        return $this->request;
    }

    public function created_at($value = null, $operator = '=')
    {
        if (!$value)
            return;

        $this->builder->where('created_at', $operator, $value);
    }

    public function created_at_from(?string $value = null): Builder
    {
        if (!$value)
            return $this->builder;
        return $this->builder->whereDate('created_at', '>=', "$value 00:00:00");
    }

    /**
     * @param string|null $value
     * @return Builder
     */
    public function created_at_to(?string $value = null): Builder
    {
        if (!$value)
            return $this->builder;
        return $this->builder->whereDate('created_at', '<=', "$value 23:59:59");
    }

    public function updated_at($value = null, $operator = '=')
    {
        if (!$value)
            return;

        $this->builder->where('updated_at', $operator, $value);
    }

    public function is_active($value = null)
    {
        if ($value == null) {
            return;
        }
        $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
        $this->builder->where('is_active', $value);
    }
}
