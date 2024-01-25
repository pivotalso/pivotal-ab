<?php

namespace pivotalso\LaravelAb\Models;

use pivotalso\LaravelAb\Events\Track;

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
        return $this->hasMany('pivotalso\LaravelAb\Models\Events');
    }

    /*public function goals(){
        return $this->hasMany('EightyTwoRules\LaravelAb\Goal', 'goal','goal');
    }*/
}
