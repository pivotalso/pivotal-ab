<?php

namespace pivotalso\PivotalAb\Models;

use Illuminate\Database\Eloquent\Model;
use pivotalso\PivotalAb\Events\Track;

class Instance extends Model
{
    protected $table = 'ab_instance';

    protected $fillable = ['instance', 'metadata', 'identifier'];

    public static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            event(new Track($model));
        });
    }

    public function events()
    {
        return $this->hasMany('pivotalso\PivotalAb\Models\Events');
    }

    public function goals()
    {
        return $this->hasMany('pivotalso\PivotalAb\Models\Goal');
    }

    public function setMetadataAttribute($value)
    {
        $this->attributes['metadata'] = is_null($value) ? null : serialize($value);
    }

    public function getMetadataAttribute($value)
    {
        return unserialize($value);
    }

    public function toExport()
    {
        return $this->toArray();
    }
}
