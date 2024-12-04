<?php

namespace App\Http\QueryFilters;

use Closure;
use Exception;
use Illuminate\Database\Eloquent\Builder;

abstract class Filter
{
    /**
     * The main function of a Laravel-implemented Pipe
     *
     * @throws Exception
     */
    public function handle(Builder $builder, Closure $next): Builder
    {
        if (! request()->has($this->getFilterName())) {
            return $next($builder);
        }

        $builder = $next($builder);

        return $this->applyFilter($builder);
    }

    /**
     * Check if the table is already joined in the query builder
     */
    protected function hasJoin(Builder $builder, string $table): bool
    {
        if (is_null($builder->getQuery()->joins)) {
            return false;
        }

        foreach ($builder->getQuery()->joins as $joinClause) {
            if ($joinClause->table === $table) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get the filter name
     */
    abstract protected function getFilterName(): string;

    /**
     * Apply the query filter
     */
    abstract protected function applyFilter(Builder $builder): Builder;
}
