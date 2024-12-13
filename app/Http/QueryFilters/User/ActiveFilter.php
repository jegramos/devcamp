<?php

namespace App\Http\QueryFilters\User;

use App\Http\QueryFilters\Filter;
use Illuminate\Database\Eloquent\Builder;

class ActiveFilter extends Filter
{
    protected function applyFilter(Builder $builder): Builder
    {
        $filter = (bool) request()->input($this->getFilterName());

        return $builder->where('users.active', $filter);
    }

    protected function getFilterName(): string
    {
        return 'active';
    }
}
