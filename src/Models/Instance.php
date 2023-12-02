<?php

namespace eighttworules\LaravelAb\Models;

use Illuminate\Database\Eloquent\Model;

class Instance extends Model
{
    protected $table = 'ab_instance';

    protected $fillable = ['instance', 'metadata', 'identifier'];

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
