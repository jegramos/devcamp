<?php

namespace App\Actions;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * This action class handles the retrieval of country records from the database.
 * The results are cached for one day.
 * <code>
 * $listOfCountries = $getCountryListAction->execute('name', 'id');
 * </code>
 */
class GetCountryListAction
{
    private string $cacheKey = 'country_list';

    public function execute(string ...$columns): Collection
    {
        return Cache::remember($this->cacheKey, now()->addDay(), function () use ($columns) {
            return DB::table('countries')->select(...$columns)->get();
        });
    }
}
