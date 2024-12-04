<?php

namespace App\Http\QueryFilters\User;

use App\Http\QueryFilters\Filter;
use Illuminate\Database\Eloquent\Builder;

class RoleFilter extends Filter
{
    protected function applyFilter(Builder $builder): Builder
    {
        $filter = request()->input($this->getFilterName());

        return $builder->whereHas('roles', function (Builder $q) use ($filter) {
            $q->where('name', $filter);
        });
    }

    protected function getFilterName(): string
    {
        return 'role';
    }
}
