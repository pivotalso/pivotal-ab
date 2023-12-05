<?php

namespace eighttworules\LaravelAb\Models;

use eighttworules\LaravelAb\Events\Track;
use Illuminate\Database\Eloquent\Model;

class Events extends Model
{
    protected $table = 'ab_events';

    protected $fillable = ['name', 'value'];

    protected $touches = ['instance'];

    public static function boot()
    {
        parent::boot();
        self::created(function($model){
            event(new Track($model));
        });
    }

    public function experiment()
    {
        return $this->belongsTo('eighttworules\LaravelAb\Models\Experiment');
    }

    public function instance()
    {
        return $this->belongsTo('eighttworules\LaravelAb\Models\Instance');
    }
}
