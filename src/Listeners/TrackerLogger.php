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
        Log::debug(json_encode(['insideTrack'=> $track->model->toArray() ], JSON_PRETTY_PRINT));
    }
}
