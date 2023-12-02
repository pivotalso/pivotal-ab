<?php

namespace eighttworules\LaravelAb\Models;

use Illuminate\Database\Eloquent\Model;

class Goal extends Model
{
    protected $table = 'ab_goal';

    protected $fillable = ['goal', 'value'];

    public function experiment()
    {
        return $this->belongsTo('eighttworules\LaravelAb\Models\Experiment');
    }

    public function instance()
    {
        return $this->belongsTo('eighttworules\LaravelAb\Models\Instance');
    }
}
