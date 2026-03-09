<?php

namespace pivotalso\PivotalAb\Jobs;

use Illuminate\Support\Facades\DB;

class GetReport
{
    public function __construct(
        protected string $experiment
    ) {}

    /** @return list<array<string, mixed>> */
    public function handle(): array
    {
        $info = [];

        $hits = DB::table('ab_events')
            ->select(DB::raw('ab_events.value, count(*) as hits'))
            ->where('ab_events.name', '=', $this->experiment)
            ->groupBy('ab_events.value')
            ->get();

        foreach ($hits as $record) {
            $info[$record->value] = [
                'condition' => $record->value,
                'hits' => $record->hits,
                'goals' => 0,
                'conversion' => 0.0,
            ];
        }

        $goals = DB::table('ab_events')
            ->select(DB::raw('ab_events.value, count(ab_events.value) as goals'))
            ->join('ab_goal', 'ab_goal.instance_id', '=', 'ab_events.instance_id')
            ->where('ab_events.name', '=', $this->experiment)
            ->groupBy('ab_events.value')
            ->get();

        foreach ($goals as $record) {
            $info[$record->value]['goals'] = $record->goals;
            $info[$record->value]['conversion'] = ($record->goals / $info[$record->value]['hits']) * 100;
        }

        usort($info, fn ($a, $b) => $b['conversion'] <=> $a['conversion']);

        return $info;
    }
}
