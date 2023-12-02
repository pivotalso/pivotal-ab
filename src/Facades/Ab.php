<?php

namespace eighttworules\LaravelAb\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \eighttworules\LaravelAb\LaravelAb
 */
class Ab extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \eighttworules\LaravelAb\LaravelAb::class;
    }
}
