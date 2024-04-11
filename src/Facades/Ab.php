<?php

namespace pivotalso\PivotalAb\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \pivotalso\PivotalAb\PivotalAb
 */
class Ab extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \pivotalso\PivotalAb\PivotalAb::class;
    }
}
