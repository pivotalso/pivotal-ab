<?php

namespace pivotalso\LaravelAb;

class EventQueue
{
    /**
     * @var static
     * Instance Object to identify user's session
     */
    protected static $events = [];


    public static function addEvent($event)
    {
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
