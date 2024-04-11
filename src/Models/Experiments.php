<?php

namespace pivotalso\PivotalAb\Models;

use pivotalso\PivotalAb\Events\Track;

class Experiments extends \Eloquent
{
    protected $table = 'ab_experiments';

    protected $fillable = ['experiment', 'goal'];

    public static function boot()
    {
        parent::boot();
        self::created(function ($model) {
            event(new Track($model));
        });
    }

    public function events()
    {
        return $this->hasMany('pivotalso\PivotalAb\Models\Events');
    }

    /*public function goals(){
        return $this->hasMany('EightyTwoRules\PivotalAb\Goal', 'goal','goal');
    }*/

    public function toExport()
    {
        return $this->toArray();
    }
}
