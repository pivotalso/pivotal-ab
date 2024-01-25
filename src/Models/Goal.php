<?php

namespace pivotalso\LaravelAb\Models;

use Illuminate\Database\Eloquent\Model;
use pivotalso\LaravelAb\Events\Track;

class Goal extends Model
{
    protected $table = 'ab_goal';

    protected $fillable = ['goal', 'value', 'instance_id'];

    protected $appends = ['instance'];

    public static function boot()
    {
        parent::boot();
        self::created(function ($model) {
            event(new Track($model));
        });
    }

    public function getInstanceAttribute()
    {
        return $this->instance()->first()->instance;
    }

    public function experiment()
    {
        return $this->belongsTo('pivotalso\LaravelAb\Models\Experiment');
    }

    public function instance()
    {
        return $this->belongsTo('pivotalso\LaravelAb\Models\Instance');
    }
}
