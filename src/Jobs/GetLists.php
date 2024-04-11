<?php

namespace pivotalso\PivotalAb\Jobs;

class GetLists
{
    /**
     * Execute the job.
     * Return a list of experiments and their hits
     */
    public function handle()
    {
        return \DB::table('ab_experiments')
            ->join('ab_events', 'ab_events.experiments_id', '=', 'ab_experiments.id')
            ->select(\DB::raw('max(ab_experiments.experiment) as experiment, count(*) as hits'))
            ->groupBy('ab_experiments.id')
            ->get();
    }
}
