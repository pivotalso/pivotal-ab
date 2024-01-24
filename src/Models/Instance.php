<?php

namespace eighttworules\LaravelAb\Models;

use eighttworules\LaravelAb\Events\Track;
use Illuminate\Database\Eloquent\Model;

class Instance extends Model
{
    protected $table = 'ab_instance';

    protected $fillable = ['instance', 'metadata', 'identifier'];

    public static function boot()
    {
        parent::boot();
        self::creating(function($model){
            event(new Track($model));
        });
    }

    public function events()
    {
        return $this->hasMany('eighttworules\LaravelAb\Models\Events');
    }

    public function goals()
    {
        return $this->hasMany('eighttworules\LaravelAb\Models\Goal');
    }

    public function setMetadataAttribute($value)
    {
        $this->attributes['metadata'] = is_null($value) ? null : serialize($value);
    }

    public function getMetadataAttribute($value)
    {
        return unserialize($value);
    }
}
