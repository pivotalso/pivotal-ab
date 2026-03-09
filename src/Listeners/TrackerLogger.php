<?php

namespace pivotalso\PivotalAb\Listeners;

use pivotalso\PivotalAb\EventQueue;
use pivotalso\PivotalAb\Events\Track;

class TrackerLogger
{
    public function handle(Track $track): void
    {
        EventQueue::addEvent($track);
    }
}
