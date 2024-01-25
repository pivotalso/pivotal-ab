<?php

namespace pivotalso\LaravelAb\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \pivotalso\LaravelAb\LaravelAb
 */
class Ab extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \pivotalso\LaravelAb\LaravelAb::class;
    }
}
