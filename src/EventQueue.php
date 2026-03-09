<?php

namespace pivotalso\PivotalAb;

class EventQueue
{
    /** @var array<int, object> */
    protected static array $events = [];

    public static function addEvent(object $event): void
    {
        self::$events[] = $event;
    }

    /** @return array<int, object> */
    public static function getEvents(): array
    {
        return self::$events;
    }

    public static function clearEvents(): void
    {
        self::$events = [];
    }
}
