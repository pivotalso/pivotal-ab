<?php

namespace pivotalso\PivotalAb\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use pivotalso\PivotalAb\Events\Track;

class Events extends Model
{
    protected $table = 'ab_events';

    protected $fillable = ['name', 'value', 'instance_id', 'experiments_id'];

    protected $touches = ['instance'];

    protected $appends = ['instance'];

    public static function boot()
    {
        parent::boot();
        self::created(function ($model) {
            Log::debug(json_encode($model->toArray(), JSON_PRETTY_PRINT));
            event(new Track($model));
        });
    }

    public function getInstanceAttribute()
    {
        $instance = $this->instance()->first();

        return ! empty($instance) ? $instance->instance : null;
    }

    public function experiment()
    {
        return $this->belongsTo('pivotalso\PivotalAb\Models\Experiments', 'experiments_id', 'id');
    }

    public function instance()
    {
        return $this->belongsTo('pivotalso\PivotalAb\Models\Instance');
    }

    public function toExport()
    {
        $data = $this->toArray();
        $data['instance'] = $this->instance()->first()->instance;
        $data['experiment'] = $this->experiment()->first()->experiment;
        return $data;
    }
}
