<?php

namespace eighttworules\LaravelAb\Models;

class Experiments extends \Eloquent
{
    protected $table = 'ab_experiments';
    protected $fillable = ['experiment', 'goal'];

    public function events()
    {
        return $this->hasMany('eighttworules\LaravelAb\Models\Events');
    }

    /*public function goals(){
        return $this->hasMany('EightyTwoRules\LaravelAb\Goal', 'goal','goal');
    }*/
}
