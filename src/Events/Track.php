<?php

namespace eighttworules\LaravelAb\Events;

use Illuminate\Queue\SerializesModels;

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
