<?php

namespace pivotalso\PivotalAb\Jobs;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class GetLists
{
    /** @return Collection<int, object> */
    public function handle(): Collection
    {
        return DB::table('ab_experiments')
            ->join('ab_events', 'ab_events.experiments_id', '=', 'ab_experiments.id')
            ->select(DB::raw('max(ab_experiments.experiment) as experiment, count(*) as hits, ab_experiments.id as id'))
            ->groupBy('ab_experiments.id')
            ->get();
    }
}
