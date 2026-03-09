<?php

namespace pivotalso\PivotalAb\Facades;

use Illuminate\Support\Facades\Facade;
use pivotalso\PivotalAb\Models\Goal;
use pivotalso\PivotalAb\Models\Instance;
use pivotalso\PivotalAb\PivotalAb;

/**
 * @method static void initUser(\Illuminate\Http\Request|null $request = null)
 * @method static string|null saveSession()
 * @method static Goal|null goal(string $goal, mixed $value = null)
 * @method static Instance|null getSession()
 * @method static void resetSession()
 * @method static PivotalAb choice(string $experiment, array<int, string> $conditions)
 * @method static void sendEvent(string $event, mixed $payload)
 *
 * @see PivotalAb
 */
class Ab extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return PivotalAb::class;
    }
}
