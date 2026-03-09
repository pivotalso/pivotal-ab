<?php

namespace pivotalso\PivotalAb\Commands;

use Illuminate\Console\Command;
use pivotalso\PivotalAb\Models\Events;
use pivotalso\PivotalAb\Models\Experiments;
use pivotalso\PivotalAb\Models\Goal;
use pivotalso\PivotalAb\Models\Instance;

class AbExport extends Command
{
    protected $signature = 'ab:export';

    protected $description = 'Exports experiment data as JSON';

    public function handle(): void
    {
        $data = [
            'experiments' => Experiments::orderBy('created_at')->get()->map(fn ($experiment) => [
                'experiment' => $experiment->experiment,
                'goal' => $experiment->goal,
                'created_at' => $experiment->created_at->toDateTimeString(),
            ]),
            'goals' => Goal::orderBy('created_at')->get()->map(fn ($goal) => [
                'goal' => $goal->goal,
                'value' => $goal->value,
                'instance' => $goal->instance,
                'created_at' => $goal->created_at->toDateTimeString(),
            ]),
            'instances' => Instance::orderBy('created_at')->get()->map(fn ($instance) => [
                'instance' => $instance->instance,
                'identifier' => $instance->identifier,
                'created_at' => $instance->created_at->toDateTimeString(),
            ]),
            'events' => Events::orderBy('created_at')->get()->map(fn ($event) => [
                'experiment' => $event->experiment()->first()?->experiment,
                'name' => $event->name,
                'value' => $event->value,
                'instance' => $event->instance,
                'created_at' => $event->created_at->toDateTimeString(),
            ]),
        ];

        $filename = sprintf('ab_export_%s.json', date('Y-m-d_H-i-s'));
        file_put_contents($filename, json_encode($data, JSON_PRETTY_PRINT));

        $this->info(sprintf('Exported to %s', $filename));
    }
}
