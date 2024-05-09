<?php

namespace pivotalso\PivotalAb;

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
        Log::debug(json_encode(['insideEventQueue'=> $event->model->toArray() ], JSON_PRETTY_PRINT));
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
