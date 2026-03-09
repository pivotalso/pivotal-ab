<?php

namespace pivotalso\PivotalAb\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use pivotalso\PivotalAb\Events\Track;

/**
 * @property int $id
 * @property string $instance
 * @property string $identifier
 * @property array<string, mixed>|null $metadata
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 *
 * @method static self firstOrCreate(array<string, mixed> $attributes, array<string, mixed> $values = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static> orderBy(string $column, string $direction = 'asc')
 */
class Instance extends Model
{
    protected $table = 'ab_instance';

    protected $fillable = ['instance', 'metadata', 'identifier'];

    protected static function booted(): void
    {
        static::creating(function (Instance $model) {
            event(new Track($model));
        });
    }

    /** @return HasMany<Events, $this> */
    public function events(): HasMany
    {
        return $this->hasMany(Events::class);
    }

    /** @return HasMany<Goal, $this> */
    public function goals(): HasMany
    {
        return $this->hasMany(Goal::class);
    }

    /** @param array<string, mixed>|null $value */
    public function setMetadataAttribute(?array $value): void
    {
        $this->attributes['metadata'] = is_null($value) ? null : serialize($value);
    }

    /** @return array<string, mixed>|null */
    public function getMetadataAttribute(?string $value): ?array
    {
        if (is_null($value)) {
            return null;
        }

        return unserialize($value);
    }

    /** @return array<string, mixed> */
    public function toExport(): array
    {
        return $this->toArray();
    }
}
