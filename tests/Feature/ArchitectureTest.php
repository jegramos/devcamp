<?php

/** @see https://pestphp.com/docs/arch-testing */
arch()
    ->expect('App')
    ->not->toUse(['die', 'dd', 'dump', 'env', 'var_dump']);

arch()
    ->expect('App\Http\Controllers')
    ->toBeClasses()
    ->ignoring('App\Http\Controllers\Traits')
    ->toExtendNothing();

arch()
    ->preset()
    ->laravel()
    ->ignoring([
        'App\Http\Controllers',
        'App\Http\QueryFilters',
        'App\Providers\TelescopeServiceProvider'
    ]);

arch()
    ->expect('App\Http\Controllers')
    ->toBeClasses()
    ->toExtendNothing()
    ->toHaveSuffix('Controller');

arch()
    ->preset()
    ->security()
    ->ignoring([
        'md5',
        'exec',
        'sha1'
    ]);
