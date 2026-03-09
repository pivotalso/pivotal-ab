<?php

namespace pivotalso\PivotalAb\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\SerializesModels;

class Track
{
    use SerializesModels;

    public function __construct(
        public Model $model
    ) {}
}
