<?php

namespace pivotalso\PivotalAb\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use pivotalso\PivotalAb\Events\Track;

/**
 * @property int $id
 * @property string $experiment
 * @property string $goal
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 *
 * @method static self firstOrCreate(array<string, mixed> $attributes, array<string, mixed> $values = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static> orderBy(string $column, string $direction = 'asc')
 */
class Experiments extends Model
{
    protected $table = 'ab_experiments';

    protected $fillable = ['experiment', 'goal'];

    protected static function booted(): void
    {
        static::created(function (Experiments $model) {
            event(new Track($model));
        });
    }

    /** @return HasMany<Events, $this> */
    public function events(): HasMany
    {
        return $this->hasMany(Events::class);
    }

    /** @return array<string, mixed> */
    public function toExport(): array
    {
        return $this->toArray();
    }
}
