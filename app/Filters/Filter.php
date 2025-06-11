<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

abstract class Filter
{

    protected $request;

 
    protected $query;

  
    public function __construct(Request $request)
    {
        $this->request = $request;
    }


    abstract public function apply(Builder $query): Builder;
}
