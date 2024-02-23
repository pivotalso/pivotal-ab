<?php

namespace pivotalso\LaravelAb\Listeners;

use Illuminate\Support\Facades\Log;
use pivotalso\LaravelAb\EventQueue;
use pivotalso\LaravelAb\Events\Track;

class TrackerLogger
{
    /**
     * Handle the event.
     */
    public function handle(Track $track)
    {
        Log::debug("trackerlogerr fired");
        Log::debug(json_encode($track));
        EventQueue::addEvent($track);
    }
}
