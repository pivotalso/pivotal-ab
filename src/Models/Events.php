<?php

namespace pivotalso\PivotalAb\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use pivotalso\PivotalAb\Events\Track;

/**
 * @property int $id
 * @property int $instance_id
 * @property int $experiments_id
 * @property string $name
 * @property string $value
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read string|null $instance
 *
 * @method static self firstOrCreate(array<string, mixed> $attributes, array<string, mixed> $values = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static> orderBy(string $column, string $direction = 'asc')
 */
class Events extends Model
{
    protected $table = 'ab_events';

    protected $fillable = ['name', 'value', 'instance_id', 'experiments_id'];

    /** @var list<string> */
    protected $touches = ['instance'];

    /** @var list<string> */
    protected $appends = ['instance'];

    protected static function booted(): void
    {
        static::created(function (Events $model) {
            event(new Track($model));
        });
    }

    public function getInstanceAttribute(): ?string
    {
        $instance = $this->instance()->first();

        return $instance?->instance;
    }

    /** @return BelongsTo<Experiments, $this> */
    public function experiment(): BelongsTo
    {
        return $this->belongsTo(Experiments::class, 'experiments_id', 'id');
    }

    /** @return BelongsTo<Instance, $this> */
    public function instance(): BelongsTo
    {
        return $this->belongsTo(Instance::class);
    }

    /** @return array<string, mixed> */
    public function toExport(): array
    {
        $data = $this->toArray();
        $data['instance'] = $this->instance()->first()?->instance;
        $data['experiment'] = $this->experiment()->first()?->experiment;

        return $data;
    }
}
