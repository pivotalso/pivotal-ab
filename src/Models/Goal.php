<?php

namespace pivotalso\PivotalAb\Models;

use Illuminate\Database\Eloquent\Model;
use pivotalso\PivotalAb\Events\Track;

class Goal extends Model
{
    protected $table = 'ab_goal';

    protected $fillable = ['goal', 'value', 'instance_id'];

    protected $casts = [
        'value' => 'array',
    ];

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
        return $this->belongsTo('pivotalso\PivotalAb\Models\Experiment');
    }

    public function instance()
    {
        return $this->belongsTo('pivotalso\PivotalAb\Models\Instance');
    }

    public function toExport()
    {
        $data = $this->toArray();
        $data['instance'] = $this->instance()->first()->instance;
        return $data;
    }
}
