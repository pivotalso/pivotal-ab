<?php

namespace eighttworules\LaravelAb\Listeners;

use eighttworules\LaravelAb\EventQueue;
use eighttworules\LaravelAb\Events\Track;

class TrackerLogger
{
    /**
     * Handle the event.
     */
    public function handle(Track $track) {
        EventQueue::addEvent($track);
    }
}
