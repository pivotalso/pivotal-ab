<?php

namespace eighttworules\LaravelAb\Models;

use eighttworules\LaravelAb\Events\Track;
use Illuminate\Database\Eloquent\Model;

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
        return $this->belongsTo('eighttworules\LaravelAb\Models\Experiment');
    }

    public function instance()
    {
        return $this->belongsTo('eighttworules\LaravelAb\Models\Instance');
    }
}
