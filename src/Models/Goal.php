<?php

namespace pivotalso\PivotalAb\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use pivotalso\PivotalAb\Events\Track;

/**
 * @property int $id
 * @property int $instance_id
 * @property string $goal
 * @property array<string, mixed>|null $value
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read string|null $instance
 *
 * @method static self create(array<string, mixed> $attributes)
 * @method static \Illuminate\Database\Eloquent\Builder<static> orderBy(string $column, string $direction = 'asc')
 */
class Goal extends Model
{
    protected $table = 'ab_goal';

    protected $fillable = ['goal', 'value', 'instance_id'];

    /** @var array<string, string> */
    protected $casts = [
        'value' => 'array',
    ];

    /** @var list<string> */
    protected $appends = ['instance'];

    protected static function booted(): void
    {
        static::created(function (Goal $model) {
            event(new Track($model));
        });
    }

    public function getInstanceAttribute(): ?string
    {
        return $this->instance()->first()?->instance;
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

        return $data;
    }
}
