<?php

namespace App\Traits;

use App\Filters\QueryFilter;
use Illuminate\Database\Eloquent\Builder;

trait Filterable
{
    /**
     * @param Builder $query
     * @param QueryFilter $filter
     * @return Builder
     */
    public function scopeFilter(Builder $query, QueryFilter $filter)
    {
        $filter->apply($query);
        return $query;
    }
}
