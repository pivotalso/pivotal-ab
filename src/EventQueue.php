<?php

namespace pivotalso\LaravelAb;

use Illuminate\Support\Facades\Log;

class EventQueue
{
    /**
     * @var static
     *             Instance Object to identify user's session
     */
    protected static $events = [];

    public static function addEvent($event)
    {
        Log::debug('EventQueue::addEvent');
        Log::debug($event);
        self::$events = [...self::$events, $event];
    }

    public static function getEvents()
    {
        return self::$events;
    }

    public static function clearEvents()
    {
        self::$events = [];
    }
}
