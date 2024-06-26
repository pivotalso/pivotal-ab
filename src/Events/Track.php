<?php

namespace pivotalso\PivotalAb\Events;

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
        $this->model = $model;
    }
}
