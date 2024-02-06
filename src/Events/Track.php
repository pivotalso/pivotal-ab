<?php

namespace pivotalso\LaravelAb\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class Track
{
    use SerializesModels;

    public $model;

    /**
     * Create a new event instance.
     */
    public function __construct($model)
    {
        Log::debug('Track::construct');
        $this->model = $model;
    }
}
