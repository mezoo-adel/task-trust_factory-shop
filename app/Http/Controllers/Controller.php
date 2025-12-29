<?php

namespace App\Http\Controllers;

abstract class Controller
{
    public int $perPage = 10;

    public function __construct()
    {
        $this->perPage = request('per_page', 10);
    }
}
