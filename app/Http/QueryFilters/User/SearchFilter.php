<?php

namespace App\Http\QueryFilters\User;

use App\Http\QueryFilters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class SearchFilter extends Filter
{
    protected function applyFilter(Builder $builder): Builder
    {
        $searchTerm = request()->input('q');
        $searchTerm = strtolower(str_replace(' ', '', $searchTerm));

        if (!$this->hasJoin($builder, 'user_profiles')) {
            $builder->join('user_profiles', 'user_profiles.user_id', '=', 'users.id');
        }

        // Search for the combined value of the given and family name
        return $builder->where(DB::raw('LOWER(CONCAT(user_profiles.given_name, user_profiles.family_name))'), 'LIKE', '%' . $searchTerm . '%');
    }

    protected function getFilterName(): string
    {
        return 'q';
    }
}
