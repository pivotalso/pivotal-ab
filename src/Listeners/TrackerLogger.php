<?php

namespace pivotalso\PivotalAb\Listeners;

use Illuminate\Support\Facades\Log;
use pivotalso\PivotalAb\EventQueue;
use pivotalso\PivotalAb\Events\Track;

class TrackerLogger
{
    /**
     * Handle the event.
     */
    public function handle(Track $track)
    {
        EventQueue::addEvent($track);
    }
}
