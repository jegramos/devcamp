<?php

namespace App\Http\QueryFilters\User;

use App\Http\QueryFilters\Filter;
use Illuminate\Database\Eloquent\Builder;

class VerifiedFilter extends Filter
{
    /**
     * {@inheritDoc}
     */
    protected function applyFilter(Builder $builder): Builder
    {
        $filter = $this->getFilterName();
        $verified = (bool) request()->input($filter);

        if ($verified) {
            return $builder->whereNotNull('users.email_verified_at');
        }

        return $builder->whereNull('users.email_verified_at');
    }

    /**
     * {@inheritDoc}
     */
    protected function getFilterName(): string
    {
        return 'verified';
    }
}
