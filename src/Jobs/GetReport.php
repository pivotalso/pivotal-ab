<?php

namespace eighttworules\LaravelAb\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;

class GetReport
{

    public $experiment;
    /**
     * Create a new job instance.
     */
    public function __construct(string $experiment)
    {
        $this->experiment = $experiment;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        return $this->printReport($this->experiment);
    }

    public function printReport($experiment)
    {
        $info = [];

        $full_count =
            \DB::table('ab_events')
                ->select(\DB::raw('ab_events.value,count(*) as hits'))
                ->where('ab_events.name', '=', (string) $experiment)
                ->groupBy('ab_events.value')
                ->get();

        foreach ($full_count as $record) {
            $info[$record->value] = [
                'condition' => $record->value,
                'hits' => $record->hits,
                'goals' => 0,
                'conversion' => 0,
            ];
        }

        $goal_count = \DB::table('ab_events')
            ->select(\DB::raw('ab_events.value,count(ab_events.value) as goals'))
            ->join('ab_goal', 'ab_goal.instance_id', '=', 'ab_events.instance_id')
            ->where('ab_events.name', '=', (string) $experiment)
            ->groupBy('ab_events.value')
            ->get();

        foreach ($goal_count as $record) {
            $info[$record->value]['goals'] = $record->goals;
            $info[$record->value]['conversion'] = ($record->goals / $info[$record->value]['hits']) * 100;
        }

        usort($info, function ($a, $b) {
            return $a['conversion'] < $b['conversion'];
        });

        return $info;
    }


}
