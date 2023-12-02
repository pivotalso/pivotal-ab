<?php

namespace eighttworules\LaravelAb\Models;

use Illuminate\Database\Eloquent\Model;

class Events extends Model
{
    protected $table = 'ab_events';

    protected $fillable = ['name', 'value'];

    protected $touches = ['instance'];

    public function experiment()
    {
        return $this->belongsTo('eighttworules\LaravelAb\Models\Experiment');
    }

    public function instance()
    {
        return $this->belongsTo('eighttworules\LaravelAb\Models\Instance');
    }
}
